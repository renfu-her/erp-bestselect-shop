<?php

namespace App\Models;

use App\Enums\Consignment\AuditStatus;
use App\Enums\Delivery\Event;
use App\Enums\Purchase\InboundStatus;
use App\Enums\Purchase\LogEventFeature;
use App\Enums\StockEvent;
use App\Helpers\IttmsDBB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PurchaseItem extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'pcs_purchase_items';
    protected $guarded = [];

    //建立採購單
    public static function createPurchase(array $newData, $operator_user_id, $operator_user_name)
    {
        if (isset($newData['purchase_id'])
            && isset($newData['product_style_id'])
            && isset($newData['title'])
            && isset($newData['sku'])
            && isset($newData['price'])
            && isset($newData['num'])
        ) {
            return IttmsDBB::transaction(function () use ($newData, $operator_user_id, $operator_user_name
            ) {
                $id = self::create([
                    "purchase_id" => $newData['purchase_id'],
                    "product_style_id" => $newData['product_style_id'],
                    "title" => $newData['title'],
                    "sku" => $newData['sku'],
                    "price" => $newData['price'],
                    "num" => $newData['num'],
                    "temp_id" => $newData['temp_id'],
                    "memo" => $newData['memo']?? null
                ])->id;

                $rePcsLSC = PurchaseLog::stockChange($newData['purchase_id'], $newData['product_style_id'], Event::purchase()->value, $id, LogEventFeature::style_add()->value, null, $newData['num'], null, $newData['title'], 'p', $operator_user_id, $operator_user_name);

                if ($rePcsLSC['success'] == 0) {
                    DB::rollBack();
                    return $rePcsLSC;
                }
                return ['success' => 1, 'error_msg' => "", 'id' => $id];
            });
        } else {
            return ['success' => 0, 'error_msg' => "未建立採購單"];
        }
    }

    //檢查商品是否被修改過
    public static function checkInputItemDirty($itemId, array $purchaseItemReq, $key) {
        $purchaseItem = PurchaseItem::where('id', '=', $itemId)
            //->select('price', 'num')
            ->get()->first();
        $purchaseItem->price = $purchaseItemReq['price'][$key];
        $purchaseItem->num = $purchaseItemReq['num'][$key];
        $purchaseItem->memo = $purchaseItemReq['memo'][$key];
        return $purchaseItem;
    }

    //檢查商品是否被修改過
    public static function checkInputItemDirtyWithoutMemo($itemId, array $purchaseItemReq, $key) {
        $purchaseItem = PurchaseItem::where('id', '=', $itemId)
            //->select('price', 'num')
            ->get()->first();
        $purchaseItem->price = $purchaseItemReq['price'][$key];
        $purchaseItem->num = $purchaseItemReq['num'][$key];
        return $purchaseItem;
    }

    public static function checkToUpdatePurchaseItemData($itemId, array $purchaseItemReq, $key, $operator_user_id, $operator_user_name)
    {
        return IttmsDBB::transaction(function () use ($itemId, $purchaseItemReq, $key, $operator_user_id, $operator_user_name
        ) {
            $purchaseItem = PurchaseItem::checkInputItemDirty($itemId, $purchaseItemReq, $key);
            if ($purchaseItem->isDirty()) {
                foreach ($purchaseItem->getDirty() as $dirtykey => $dirtyval) {
                    $event = '';
                    $logEventFeature = null;
                    if ($dirtykey == 'price') {
                        $event = '修改價錢';
                        $logEventFeature = LogEventFeature::style_change_price()->value;
                    } else if($dirtykey == 'num') {
                        $event = '修改數量';
                        $logEventFeature = LogEventFeature::style_change_qty()->value;
                    }
                    if ('' != $event && null != $logEventFeature) {
                        //若有修改修改採購數量和總價 則需計算成本價寫回入庫單的unit_cost
                        if ($dirtykey == 'price' || $dirtykey == 'num') {
                            $unit_cost = ($purchaseItem->price / $purchaseItem->num);
                            PurchaseInbound::where('event', '=', Event::purchase()->value)
                                ->where('event_id', '=', $purchaseItem->purchase_id)
                                ->where('event_item_id', '=', $itemId)
                                ->update([
                                    'unit_cost' => $unit_cost
                                ]);
                        }

                        $rePcsLSC = PurchaseLog::stockChange($purchaseItem->purchase_id, $purchaseItem->product_style_id
                            , Event::purchase()->value, $itemId
                            , $logEventFeature, null, $dirtyval, $event
                            , $purchaseItem->title, 'p'
                            , $operator_user_id, $operator_user_name);
                        if ($rePcsLSC['success'] == 0) {
                            DB::rollBack();
                            return $rePcsLSC;
                        }
                    }
                }
                PurchaseItem::where('id', $itemId)->update([
                    "price" => $purchaseItemReq['price'][$key],
                    "num" => $purchaseItemReq['num'][$key],
                    "memo" => $purchaseItemReq['memo'][$key],
                ]);
            }
            return ['success' => 1, 'error_msg' => ''];
        });
    }

    public static function deleteItems($purchase_id, array $del_item_id_arr, $operator_user_id, $operator_user_name) {
        if (0 < count($del_item_id_arr)) {
            //判斷若其一有到貨 則不可刪除
            $arrived_num = 0;
            $items = PurchaseItem::whereIn('id', $del_item_id_arr)->get();
            foreach ($items as $item) {
                $arrived_num += $item->arrived_num ?? 0;
            }

            if (0 < $arrived_num) {
                return ['success' => 0, 'error_msg' => "有入庫 不可刪除"];
            } else {
                return IttmsDBB::transaction(function () use ($items, $purchase_id, $del_item_id_arr, $operator_user_id, $operator_user_name
                ) {
                    PurchaseItem::whereIn('id', $del_item_id_arr)->delete();
                    foreach ($items as $item) {
                        PurchaseLog::stockChange($purchase_id, $item->product_style_id, Event::purchase()->value, $item->id, LogEventFeature::style_del()->value, null, null, null, $item->title, 'p', $operator_user_id, $operator_user_name);
                    }
                    return ['success' => 1, 'error_msg' => ""];
                });
            }
        } else {
            return ['success' => 0, 'error_msg' => "刪除數量為0"];
        }
    }

    //刪除商品款式和對應入庫單
    public static function deleteItemAndInbound($item_id, $operator_user_id, $operator_user_name) {
        $pcs_item = PurchaseItem::where('id', '=', $item_id)->get()->first();
        if (false == isset($pcs_item)) {
            return ['success' => 0, 'error_msg' => '查無採購單該商品款式'];
        }
        $payingOrderList = PayingOrder::getPayingOrdersWithPurchaseID($pcs_item->purchase_id)->get();
        if (null != $payingOrderList && 0 < count($payingOrderList)) {
            return ['success' => 0, 'error_msg' => '已有付款單無法刪除', 'item_id' => $item_id];
        } else {
            DB::beginTransaction();
            $inboundList = PurchaseInbound::getInboundList(['event' => Event::purchase()->value, 'event_id' => $pcs_item->purchase_id, 'event_item_id' => $pcs_item->id])
                ->get()->toArray();
            $inbound_ids = [];
            if (0 < count($inboundList)) {
                foreach ($inboundList as $key_ib => $val_ib) {
                    if (0 < $val_ib->shipped_num) {
                        DB::rollBack();
                        return ['success' => 0, 'error_msg' => "已出貨無法刪除", 'item_id' => $pcs_item->id];
                    } else {
                        $inbound_ids[] = $val_ib->inbound_id;
                        //$can_tally = Depot::can_tally($val_ib->depot_id);
                        $can_tally = true;
                        $updateLog = PurchaseInbound::addLogAndUpdateStock(LogEventFeature::purchase_del()->value, $val_ib->inbound_id
                            , $val_ib->event, $val_ib->event_id, $val_ib->event_item_id
                            , $val_ib->product_style_id
                            , $val_ib->inbound_prd_type, $val_ib->product_title, $val_ib->inbound_num * -1, $can_tally, '刪除採購單', StockEvent::purchase_del()->value, '刪除採購單', $operator_user_id, $operator_user_name);
                        if ($updateLog['success'] == 0) {
                            DB::rollBack();
                            return $updateLog;
                        }
                    }
                }
            }

            PurchaseItem::where('id', '=', $pcs_item->id)->delete();
            PurchaseInbound::whereIn('id', $inbound_ids)->where('event', '=', Event::purchase()->value)->delete();
            DB::commit();
            return ['success' => 1, 'error_msg' => ""];
        }
    }

    //更新到貨數量
    public static function updateArrivedNum($id, $addnum, $can_tally = false) {
        DB::beginTransaction();
        try {
            $updateArr = [];
            $updateArr['arrived_num'] = DB::raw("arrived_num + $addnum");
            if (true == $can_tally) {
                $updateArr['tally_num'] = DB::raw("tally_num + $addnum");
            }
            PurchaseItem::where('id', $id)
                ->update($updateArr);

            DB::commit();
            return ['success' => 1, 'error_msg' => ""];
        } catch (\Exception $e) {
            DB::rollback();
            return ['success' => 0, 'error_msg' => $e->getMessage()];
        }
    }

    public static function getDataForInbound($purchase_id) {
        $raw = '( COALESCE(items.num, 0) - COALESCE(items.arrived_num, 0) )';
        $result = DB::table('pcs_purchase_items as items')
            ->select('items.id'
                , 'items.purchase_id'
                , 'items.product_style_id'
                , 'items.title'
                , 'items.sku'
                , 'items.num'
                , 'items.arrived_num'
                , DB::raw($raw. ' as should_enter_num')
            )
//            ->where(DB::raw($raw), '>', 0)
            ->where('purchase_id', $purchase_id)
            ->whereNull('deleted_at');

        return $result;
    }

    //採購 明細(會鋪出全部的採購商品)
    //******* 修改時請一併修改採購 總表
    public static function getPurchaseDetailList(
          $purchase_id = null
        , $purchase_item_id = null
        , $purchase_sn = null
        , $title = null
//        , $sku = null
        , $purchase_user_id = []
        , $purchase_sdate = null
        , $purchase_edate = null
        , $supplier_id = null
        , $estimated_depot_id = null
        , $depot_id = null
        , $inbound_user_id = []
        , $inbound_status = []
        , $inbound_sdate = null
        , $inbound_edate = null
        , $expire_day = null
        , $audit_status = null
    ) {

        //訂金單號
        $subColumn = DB::table('pcs_paying_orders as order')
            ->select('order.sn')
            ->whereColumn('order.source_id', '=', 'purchase.id')
            ->where([
                'order.source_type'=>DB::raw('"'. app(Purchase::class)->getTable().'"'),
                'order.type'=>DB::raw('0'),
            ])
            ->whereNull('order.deleted_at')
            ->orderByDesc('order.id')
            ->limit(1);
        //尾款單號
        $subColumn2 = DB::table('pcs_paying_orders as order')
            ->select('order.sn')
            ->whereColumn('order.source_id', '=', 'purchase.id')
            ->where([
                'order.source_type'=>DB::raw('"'. app(Purchase::class)->getTable().'"'),
                'order.type'=>DB::raw('1'),
            ])
            ->whereNull('order.deleted_at')
            ->orderByDesc('order.id')
            ->limit(1);

        $tempInboundSql = DB::table('pcs_purchase_inbound as inbound')
            ->select('event_id'
                , 'event_item_id'
                , 'product_style_id')
            ->selectRaw('sum(inbound_num) as inbound_num')
            ->selectRaw('DATE_FORMAT((min(expiry_date)),"%Y-%m-%d") as expiry_date')
            ->selectRaw('GROUP_CONCAT(DISTINCT inbound.inbound_user_name) as inbound_user_names') //入庫人員
            ->whereNull('deleted_at');

        $tempInboundSql->where('inbound.event', '=', Event::purchase()->value);

        $tempInboundSql->groupBy('event_id')
            ->groupBy('event_item_id')
            ->groupBy('product_style_id');
        if ($depot_id) {
            $tempInboundSql->where('inbound.depot_id', '=', $depot_id);
        }
        if ($inbound_user_id) {
            $tempInboundSql->whereIn('inbound.inbound_user_id', $inbound_user_id);
        }
        if ($inbound_sdate && $inbound_edate) {
            $sDate = date('Y-m-d 00:00:00', strtotime($inbound_sdate));
            $eDate = date('Y-m-d 23:59:59', strtotime($inbound_edate));
            $tempInboundSql->whereBetween('inbound.inbound_date', [$sDate, $eDate]);
        }
        if ($expire_day) {
            if (0 < $expire_day) {
                //大於0 找近N天
                $tempInboundSql->whereBetween('inbound.expiry_date', [DB::raw('NOW()'), DB::raw('date_add(now(), interval '. $expire_day. ' day)')]);
            } else if (0 > $expire_day) {
                //小於0 找過期
                $tempInboundSql->where('inbound.expiry_date', '<=', $expire_day);
            }
        }

        $query_not_yet = 'COALESCE((items.arrived_num), 0) = 0';
        $query_normal = '( COALESCE(items.num, 0) - COALESCE((items.arrived_num), 0) ) = 0 and COALESCE((items.arrived_num), 0) <> 0';
        $query_shortage = 'COALESCE(items.num, 0) > COALESCE(items.arrived_num, 0)';
        $query_overflow = 'COALESCE(items.num, 0) < COALESCE(items.arrived_num, 0)';

        $result = DB::table('pcs_purchase as purchase')
            ->leftJoin('pcs_purchase_items as items', 'purchase.id', '=', 'items.purchase_id')
            ->leftJoin('prd_product_styles as style', 'style.id', '=', 'items.product_style_id')
            ->leftJoinSub($tempInboundSql, 'inbound', function($join) use($tempInboundSql) {
                $join->on('items.purchase_id', '=', 'inbound.event_id')
                    ->on('items.id', '=', 'inbound.event_item_id')
                    ->on('items.product_style_id', '=', 'inbound.product_style_id');
            })
            //->select('*')
            ->select('purchase.id as id'
                ,'purchase.sn as sn'
                ,'items.id as items_id'
                ,'items.product_style_id as product_style_id'
                ,'items.title as title'
                ,'items.sku as sku'
                ,'items.price as price'
                ,'items.num as num'
                ,'items.arrived_num as arrived_num'
                ,'items.memo as memo'
                ,'inbound.expiry_date'
                ,'inbound.inbound_user_names'
                ,'purchase.purchase_user_id as purchase_user_id'
                ,'purchase.supplier_id as supplier_id'
                ,'purchase.invoice_num as invoice_num'
                ,'purchase.purchase_user_name as purchase_user_name'
                ,'purchase.supplier_name as supplier_name'
                ,'purchase.supplier_nickname as supplier_nickname'
                ,'purchase.estimated_depot_id as estimated_depot_id'
                ,'purchase.estimated_depot_name as estimated_depot_name'
                ,'style.estimated_cost as estimated_cost'
                , DB::raw('(case
                    when purchase.audit_status ='. AuditStatus::unreviewed()->value. ' then "'. AuditStatus::getDescription(AuditStatus::unreviewed()->value). '"
                    when purchase.audit_status ='. AuditStatus::approved()->value. ' then "'. AuditStatus::getDescription(AuditStatus::approved()->value). '"
                    when purchase.audit_status ='. AuditStatus::veto()->value. ' then "'. AuditStatus::getDescription(AuditStatus::veto()->value). '"
                    end ) as audit_status')

            )
            ->selectRaw('DATE_FORMAT(purchase.created_at,"%Y-%m-%d") as created_at')
            ->selectRaw('DATE_FORMAT(purchase.scheduled_date,"%Y-%m-%d") as scheduled_date')
            ->selectRaw('convert(items.price / items.num, decimal) as single_price')
            ->selectRaw('(items.num - items.arrived_num) as error_num')
            ->selectRaw('(case
                    when '. $query_not_yet. ' then "'. InboundStatus::getDescription(InboundStatus::not_yet()->value). '"
                    when '. $query_normal. ' then "'. InboundStatus::getDescription(InboundStatus::normal()->value). '"
                    when '. $query_shortage. ' then "'. InboundStatus::getDescription(InboundStatus::shortage()->value). '"
                    when '. $query_overflow. ' then "'. InboundStatus::getDescription(InboundStatus::overflow()->value). '"
                end) as inbound_status')
            ->addSelect(['deposit_num' => $subColumn, 'final_pay_num' => $subColumn2])
            ->whereNull('purchase.deleted_at')
            ->whereNull('items.deleted_at');

        if ($estimated_depot_id) {
            $result->where('purchase.estimated_depot_id', '=', $estimated_depot_id);
        }
        if ($purchase_id) {
            $result->where('purchase.id', '=', $purchase_id);
        }
        if ($purchase_item_id) {
            $result->where('items.id', '=', $purchase_item_id);
        }
        if($purchase_sn) {
            $result->where('purchase.sn', '=', $purchase_sn);
        }
        if($title) {
            $result->where(function ($query) use ($title) {
                $query->Where('items.title', 'like', "%{$title}%");
                $query->orWhere('items.sku', 'like', "%{$title}%");
            });
        }
        if ($purchase_user_id) {
            $result->whereIn('purchase.purchase_user_id', $purchase_user_id);
        }
        if ($purchase_sdate && $purchase_edate) {
            $sDate = date('Y-m-d 00:00:00', strtotime($purchase_sdate));
            $eDate = date('Y-m-d 23:59:59', strtotime($purchase_edate));
            $result->whereBetween('purchase.created_at', [$sDate, $eDate]);
        }
        if ($supplier_id) {
            $result->where('purchase.supplier_id', '=', $supplier_id);
        }
        if (isset($audit_status)) {
            $result->where('purchase.audit_status', $audit_status);
        }
        if ($expire_day) {
            $result->whereNotNull('inbound.expiry_date');
        }

        $result2 = DB::table(DB::raw("({$result->toSql()}) as tb"))
            ->select('*')
            ->orderByDesc('id')
            ->orderBy('items_id')
        ;

        $result->mergeBindings($subColumn);
        $result->mergeBindings($subColumn2);
        $result2->mergeBindings($result);

        if ($inbound_status) {
            $arr_status = [];
            if (in_array(InboundStatus::not_yet()->value, $inbound_status)) {
                array_push($arr_status, InboundStatus::getDescription(InboundStatus::not_yet()->value));
            }
            if (in_array(InboundStatus::normal()->value, $inbound_status)) {
                array_push($arr_status, InboundStatus::getDescription(InboundStatus::normal()->value));
            }
            if (in_array(InboundStatus::shortage()->value, $inbound_status)) {
                array_push($arr_status, InboundStatus::getDescription(InboundStatus::shortage()->value));
            }
            if (in_array(InboundStatus::overflow()->value, $inbound_status)) {
                array_push($arr_status, InboundStatus::getDescription(InboundStatus::overflow()->value));
            }

            $result2->whereIn('inbound_status', $arr_status);
        }
        return $result2;
    }

    //採購 總表(同樣的採購單號只能顯示一次)
    //******* 修改時請一併修改採購 明細
    public static function getPurchaseOverviewList(
          $purchase_sn = null
        , $title = null
        , $purchase_user_id = []
        , $purchase_sdate = null
        , $purchase_edate = null
        , $supplier_id = null
        , $estimated_depot_id = null
        , $depot_id = null
        , $inbound_user_id = []
        , $inbound_status = []
        , $inbound_sdate = null
        , $inbound_edate = null
        , $expire_day = null
        , $audit_status = null
    ) {
        //訂金單號
        $subColumn = DB::table('pcs_paying_orders as order')
            ->select('order.sn')
            ->whereColumn('order.source_id', '=', 'purchase.id')
            ->where([
                'order.source_type'=>DB::raw('"'. app(Purchase::class)->getTable().'"'),
                'order.type'=>DB::raw('0'),
            ])
            ->whereNull('order.deleted_at')
            ->orderByDesc('order.id')
            ->limit(1);
        //尾款單號
        $subColumn2 = DB::table('pcs_paying_orders as order')
            ->select('order.sn')
            ->whereColumn('order.source_id', '=', 'purchase.id')
            ->where([
                'order.source_type'=>DB::raw('"'. app(Purchase::class)->getTable().'"'),
                'order.type'=>DB::raw('1'),
            ])
            ->whereNull('order.deleted_at')
            ->orderByDesc('order.id')
            ->limit(1);

        $tempInboundSql = DB::table('pcs_purchase_inbound as inbound')
            ->select('event_id'
                , 'product_style_id')
            ->selectRaw('sum(inbound_num) as inbound_num')
            ->selectRaw('DATE_FORMAT((min(expiry_date)),"%Y-%m-%d") as expiry_date')
            ->selectRaw('GROUP_CONCAT(DISTINCT inbound.inbound_user_id) as inbound_user_ids') //入庫人員
            ->selectRaw('GROUP_CONCAT(DISTINCT inbound.inbound_user_name) as inbound_user_names') //入庫人員
            ->where('event', Event::purchase()->value)
            ->whereNull('deleted_at')
            ->groupBy('event_id');
        if ($depot_id) {
            $tempInboundSql->where('inbound.depot_id', '=', $depot_id);
        }
        if ($inbound_user_id) {
            $tempInboundSql->whereIn('inbound.inbound_user_id', $inbound_user_id);
        }
        if ($inbound_sdate && $inbound_edate) {
            $sDate = date('Y-m-d 00:00:00', strtotime($inbound_sdate));
            $eDate = date('Y-m-d 23:59:59', strtotime($inbound_edate));
            $tempInboundSql->whereBetween('inbound.inbound_date', [$sDate, $eDate]);
        }
        if ($expire_day) {
            if (0 < $expire_day) {
                //大於0 找近N天
                $tempInboundSql->whereBetween('inbound.expiry_date', [DB::raw('NOW()'), DB::raw('date_add(now(), interval '. $expire_day. ' day)')]);
            } else if (0 > $expire_day) {
                //小於0 找過期
                $tempInboundSql->where('inbound.expiry_date', '<=', $expire_day);
            }
        }

        $query_not_yet = 'COALESCE((itemtb_new.arrived_num), 0) = 0';
        $query_normal = '( COALESCE(itemtb_new.num, 0) - COALESCE((itemtb_new.arrived_num), 0) ) = 0 and COALESCE((itemtb_new.arrived_num), 0) <> 0';
        $query_shortage = 'COALESCE(itemtb_new.num, 0) > COALESCE(itemtb_new.arrived_num, 0)';
        $query_overflow = 'COALESCE(itemtb_new.num, 0) < COALESCE(itemtb_new.arrived_num, 0)';

        //為了只撈出一筆，獨立出來寫sub query
        $tempPurchaseItemSql = DB::table('pcs_purchase_items as items')
            ->leftJoinSub($tempInboundSql, 'inbound', function($join) use($tempInboundSql) {
                $join->on('items.purchase_id', '=', 'inbound.event_id')
                    ->on('items.product_style_id', '=', 'inbound.product_style_id');
            })
            ->select('items.id as id'
                , 'items.purchase_id as purchase_id'
                , 'items.product_style_id as product_style_id'
                , 'items.title as title'
                , 'items.sku as sku'
                , 'items.price as price'
                , 'items.num as num'
                , 'items.arrived_num as arrived_num'
                , 'inbound.expiry_date as expiry_date'
                , 'inbound.inbound_num as inbound_num'
                , 'inbound.inbound_user_ids as inbound_user_ids'
                , 'inbound.inbound_user_names as inbound_user_names'
            )
            ->whereNull('items.deleted_at')
            ->orderBy('items.product_style_id')
            ->groupBy('items.purchase_id');

        if($title) {
            $tempPurchaseItemSql->where(function ($query) use ($title) {
                $query->Where('items.title', 'like', "%{$title}%");
                $query->orWhere('items.sku', 'like', "%{$title}%");
            });
        }

        $result = DB::table('pcs_purchase as purchase')
            ->leftJoinSub($tempPurchaseItemSql, 'itemtb_new', function($join) use($tempPurchaseItemSql) {
                $join->on('itemtb_new.purchase_id', '=', 'purchase.id');
            })
            //->select('*')
            ->select('purchase.id as id'
                ,'purchase.sn as sn'
                ,'itemtb_new.id as items_id'
                ,'itemtb_new.title as title'
                ,'itemtb_new.sku as sku'
                ,'itemtb_new.price as price'
                ,'itemtb_new.num as num'
                ,'itemtb_new.arrived_num as arrived_num'
                ,'itemtb_new.expiry_date'
                ,'itemtb_new.inbound_user_ids as inbound_user_ids'
                ,'itemtb_new.inbound_user_names as inbound_user_names'
                ,'purchase.purchase_user_id as purchase_user_id'
                ,'purchase.supplier_id as supplier_id'
                ,'purchase.invoice_num as invoice_num'
                ,'purchase.purchase_user_name as purchase_user_name'
                ,'purchase.supplier_name as supplier_name'
                ,'purchase.supplier_nickname as supplier_nickname'
                ,'purchase.estimated_depot_id as estimated_depot_id'
                ,'purchase.estimated_depot_name as estimated_depot_name'
                , DB::raw('(case
                    when purchase.audit_status ='. AuditStatus::unreviewed()->value. ' then "'. AuditStatus::getDescription(AuditStatus::unreviewed()->value). '"
                    when purchase.audit_status ='. AuditStatus::approved()->value. ' then "'. AuditStatus::getDescription(AuditStatus::approved()->value). '"
                    when purchase.audit_status ='. AuditStatus::veto()->value. ' then "'. AuditStatus::getDescription(AuditStatus::veto()->value). '"
                    end ) as audit_status')
            )
            ->selectRaw('DATE_FORMAT(purchase.created_at,"%Y-%m-%d") as created_at')
            ->selectRaw('DATE_FORMAT(purchase.scheduled_date,"%Y-%m-%d") as scheduled_date')
            ->selectRaw('convert(itemtb_new.price / itemtb_new.num, decimal) as single_price')
            ->selectRaw('(itemtb_new.num - itemtb_new.arrived_num) as error_num')
            ->selectRaw('(case
                    when '. $query_not_yet. ' then "'. InboundStatus::getDescription(InboundStatus::not_yet()->value). '"
                    when '. $query_normal. ' then "'. InboundStatus::getDescription(InboundStatus::normal()->value). '"
                    when '. $query_shortage. ' then "'. InboundStatus::getDescription(InboundStatus::shortage()->value). '"
                    when '. $query_overflow. ' then "'. InboundStatus::getDescription(InboundStatus::overflow()->value). '"
                end) as inbound_status')
            ->addSelect(['deposit_num' => $subColumn, 'final_pay_num' => $subColumn2])
            ->whereNull('purchase.deleted_at')
            ->orderByDesc('purchase.id');

        if ($estimated_depot_id) {
            $result->where('purchase.estimated_depot_id', '=', $estimated_depot_id);
        }
        if($purchase_sn) {
            $result->where('purchase.sn', '=', $purchase_sn);
        }
        if ($purchase_user_id) {
            $result->whereIn('purchase.purchase_user_id', $purchase_user_id);
        }
        if ($purchase_sdate && $purchase_edate) {
            $sDate = date('Y-m-d 00:00:00', strtotime($purchase_sdate));
            $eDate = date('Y-m-d 23:59:59', strtotime($purchase_edate));
            $result->whereBetween('purchase.created_at', [$sDate, $eDate]);
        }
        if ($supplier_id) {
            $result->where('purchase.supplier_id', '=', $supplier_id);
        }

        if ($inbound_user_id) {
            $result->whereIn('itemtb_new.inbound_user_ids', $inbound_user_id);
        }
        if (isset($audit_status)) {
            $result->where('purchase.audit_status', $audit_status);
        }
        if ($expire_day) {
            $result->whereNotNull('itemtb_new.expiry_date');
        }

        $result2 = DB::table(DB::raw("({$result->toSql()}) as tb"))
            ->select('*')
            ->orderByDesc('id')
            ->orderBy('items_id');

        $result->mergeBindings($subColumn);
        $result->mergeBindings($subColumn2);
        $result2->mergeBindings($result);

        if ($inbound_status) {
            $arr_status = [];
            if (in_array(InboundStatus::not_yet()->value, $inbound_status)) {
                array_push($arr_status, InboundStatus::getDescription(InboundStatus::not_yet()->value));
            }
            if (in_array(InboundStatus::normal()->value, $inbound_status)) {
                array_push($arr_status, InboundStatus::getDescription(InboundStatus::normal()->value));
            }
            if (in_array(InboundStatus::shortage()->value, $inbound_status)) {
                array_push($arr_status, InboundStatus::getDescription(InboundStatus::shortage()->value));
            }
            if (in_array(InboundStatus::overflow()->value, $inbound_status)) {
                array_push($arr_status, InboundStatus::getDescription(InboundStatus::overflow()->value));
            }

            $result2->whereIn('inbound_status', $arr_status);
        }

        return $result2;
    }

    //採購商品負責人列表
    public static function getPurchaseChargemanList($purchase_id) {
        $result = DB::table('pcs_purchase_items as items')
            ->leftJoin('prd_product_styles as styles', 'styles.id', '=', 'items.product_style_id')
            ->leftJoin('prd_products as products', 'products.id', '=', 'styles.product_id')
            ->leftJoin('usr_users as users', 'users.id', '=', 'products.user_id')
            ->select('users.id as user_id'
                , 'users.name as user_name'
            )
            ->where('items.purchase_id', '=',  $purchase_id)
            ->whereNull('items.deleted_at');

        return $result;
    }

    public static function getPurchaseItemsByPurchaseId($purchaseId)
    {
        $result = DB::table('pcs_purchase_items as pcs_items')
            ->where('pcs_items.purchase_id', '=', $purchaseId)
            ->whereNull('pcs_items.deleted_at')
            ->leftJoin('prd_product_styles as prd_styles', 'pcs_items.product_style_id', '=', 'prd_styles.id')
            ->leftJoin('prd_products', 'prd_styles.product_id', '=', 'prd_products.id')
            ->leftJoin('usr_users', 'prd_products.user_id', '=', 'usr_users.id')
            ->select(
                'pcs_items.title',
                'pcs_items.price as total_price',
                'pcs_items.num',
                'pcs_items.memo',
                'pcs_items.ro_note',
                'pcs_items.po_note',
                'pcs_items.product_style_id as style_ids',
                'usr_users.name',
            )
            ->get();

        return $result;
    }


    public static function update_purchase_item($parm)
    {
        $update = [];
        if(Arr::exists($parm, 'note')){
            $update['memo'] = $parm['note'];
        }
        if(Arr::exists($parm, 'ro_note')){
            $update['ro_note'] = $parm['ro_note'];
        }
        if(Arr::exists($parm, 'po_note')){
            $update['po_note'] = $parm['po_note'];
        }

        self::where('id', $parm['purchase_item_id'])->update($update);
    }
}
