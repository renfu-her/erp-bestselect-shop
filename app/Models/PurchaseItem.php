<?php

namespace App\Models;

use App\Enums\Delivery\Event;
use App\Enums\Purchase\InboundStatus;
use App\Enums\Purchase\LogEvent;
use App\Enums\Purchase\LogEventFeature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseItem extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'pcs_purchase_items';
    protected $guarded = [];

    //建立採購單
    public static function createPurchase(array $newData, $operator_user_id, $operator_user_name)
    {
        if (isset($newData['purchase_id'])
            && $newData['product_style_id']
            && $newData['title']
            && $newData['sku']
            && $newData['price']
            && $newData['num']
        ) {
            return DB::transaction(function () use ($newData, $operator_user_id, $operator_user_name
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

                $rePcsLSC = PurchaseLog::stockChange($newData['purchase_id'], $newData['product_style_id'], LogEvent::purchase()->value, $id, LogEventFeature::style_add()->value, $newData['num'], null, $operator_user_id, $operator_user_name);

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

    public static function checkToUpdatePurchaseItemData($itemId, array $purchaseItemReq, $key, string $changeStr, $operator_user_id, $operator_user_name)
    {
        return DB::transaction(function () use ($itemId, $purchaseItemReq, $key, $changeStr, $operator_user_id, $operator_user_name
        ) {
            $purchaseItem = PurchaseItem::where('id', '=', $itemId)
                //->select('price', 'num')
                ->get()->first();
            $purchaseItem->price = $purchaseItemReq['price'][$key];
            $purchaseItem->num = $purchaseItemReq['num'][$key];
            $purchaseItem->memo = $purchaseItemReq['memo'][$key];
            if ($purchaseItem->isDirty()) {
                foreach ($purchaseItem->getDirty() as $dirtykey => $dirtyval) {
                    $changeStr .= ' itemID:' . $itemId . ' ' . $dirtykey . ' change to ' . $dirtyval;
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
                        $rePcsLSC = PurchaseLog::stockChange($purchaseItem->purchase_id, $purchaseItem->product_style_id
                            , LogEvent::purchase()->value, $itemId
                            , $logEventFeature, $dirtyval, $event
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
            return ['success' => 1, 'error_msg' => $changeStr];
        });
    }

    public static function deleteItems($purchase_id, array $del_item_id_arr, $operator_user_id, $operator_user_name) {
        if (0 < count($del_item_id_arr)) {
            //判斷若其一有到貨 則不可刪除
            $query = PurchaseItem::whereIn('id', $del_item_id_arr)
                ->selectRaw('sum(arrived_num) as arrived_num')->get()->first();
            if (0 < $query->arrived_num) {
                return ['success' => 0, 'error_msg' => "有入庫 不可刪除"];
            } else {
                return DB::transaction(function () use ($purchase_id, $del_item_id_arr, $operator_user_id, $operator_user_name
                ) {
                    PurchaseItem::whereIn('id', $del_item_id_arr)->delete();
                    foreach ($del_item_id_arr as $del_id) {
                        PurchaseLog::stockChange($purchase_id, null, LogEvent::purchase()->value, $del_id, LogEventFeature::style_del()->value, null, null, $operator_user_id, $operator_user_name);
                    }
                });
            }
        }
    }

    //更新到貨數量
    public static function updateArrivedNum($id, $addnum, $can_tally = false) {
        return DB::transaction(function () use ($id, $addnum, $can_tally
        ) {
            $updateArr = [];
            $updateArr['arrived_num'] = DB::raw("arrived_num + $addnum");
            if (true == $can_tally) {
                $updateArr['tally_num'] = DB::raw("tally_num + $addnum");
            }
            PurchaseItem::where('id', $id)
                ->update($updateArr);
            return ['success' => 1, 'error_msg' => ""];
        });
    }

    public static function getData($purchase_id) {
        return self::where('purchase_id', $purchase_id)->whereNull('deleted_at');
    }

    public static function getDataWithInbound($purchase_id) {
        $inboundOverviewList = PurchaseInbound::getOverviewInboundList(Event::purchase()->value, $purchase_id);
        $query = DB::table('pcs_purchase_items as items')
            ->leftJoinSub($inboundOverviewList, 'inbound', function($join) {
                $join->on('inbound.purchase_id', '=', 'items.purchase_id')
                    ->on('inbound.product_style_id', '=', 'items.product_style_id');
            })
            ->where('items.purchase_id', $purchase_id)
            ->whereNull('items.deleted_at');
//        dd($query->get());
        return $query;
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
            ->where(DB::raw($raw), '>', 0)
            ->where('purchase_id', $purchase_id)
            ->whereNull('deleted_at');

        return $result;
    }

    //採購 明細(會鋪出全部的採購商品)
    //******* 修改時請一併修改採購 總表
    public static function getPurchaseDetailList(
          $purchase_sn = null
        , $title = null
//        , $sku = null
        , $purchase_user_id = []
        , $purchase_sdate = null
        , $purchase_edate = null
        , $supplier_id = null
        , $depot_id = null
        , $inbound_user_id = []
        , $inbound_status = []
        , $inbound_sdate = null
        , $inbound_edate = null
        , $expire_day = null
    ) {

        //訂金單號
        $subColumn = DB::table('pcs_paying_orders as order')
            ->select('order.id')
            ->whereColumn('order.purchase_id', '=', 'purchase.id')
            ->where('order.type', '=', DB::raw('0'))
            ->whereNull('order.deleted_at')
            ->orderByDesc('order.id')
            ->limit(1);
        //尾款單號
        $subColumn2 = DB::table('pcs_paying_orders as order')
            ->select('order.id')
            ->whereColumn('order.purchase_id', '=', 'purchase.id')
            ->where('order.type', '=', DB::raw('1'))
            ->whereNull('order.deleted_at')
            ->orderByDesc('order.id')
            ->limit(1);

        $tempInboundSql = DB::table('pcs_purchase_inbound as inbound')
            ->select('event_id'
                , 'product_style_id')
            ->selectRaw('sum(inbound_num) as inbound_num')
            ->whereNull('deleted_at');

        $tempInboundSql->where('inbound.event', '=', Event::purchase()->value);

        $tempInboundSql->groupBy('event_id')
            ->groupBy('product_style_id');
        if ($depot_id) {
            $tempInboundSql->where('inbound.depot_id', '=', $depot_id);
        }
        if ($inbound_user_id) {
            $tempInboundSql->whereIn('inbound.inbound_user_id', $inbound_user_id);
        }
        if ($inbound_sdate && $inbound_edate) {
            $tempInboundSql->whereBetween('inbound.inbound_date', [date((string) $inbound_sdate), date((string) $inbound_edate)]);
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
            ->leftJoinSub($tempInboundSql, 'inbound', function($join) use($tempInboundSql) {
                $join->on('items.purchase_id', '=', 'inbound.event_id')
                    ->on('items.product_style_id', '=', 'inbound.product_style_id');
            })
            //->select('*')
            ->select('purchase.id as id'
                ,'purchase.sn as sn'
                ,'items.id as items_id'
                ,'items.title as title'
                ,'items.sku as sku'
                ,'items.price as price'
                ,'items.num as num'
                ,'items.arrived_num as arrived_num'
                ,'purchase.purchase_user_id as purchase_user_id'
                ,'purchase.supplier_id as supplier_id'
                ,'purchase.invoice_num as invoice_num'
                ,'purchase.purchase_user_name as purchase_user_name'
                ,'purchase.supplier_name as supplier_name'
                ,'purchase.supplier_nickname as supplier_nickname'

            )
            ->selectRaw('DATE_FORMAT(purchase.created_at,"%Y-%m-%d") as created_at')
            ->selectRaw('DATE_FORMAT(purchase.scheduled_date,"%Y-%m-%d") as scheduled_date')
            ->selectRaw('FORMAT(items.price / items.num, 2) as single_price')
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
            $result->whereBetween('purchase.created_at', [date((string) $purchase_sdate), date((string) $purchase_edate)]);
        }
        if ($supplier_id) {
            $result->where('purchase.supplier_id', '=', $supplier_id);
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

    //採購 總表(同樣的採購單號只能顯示一次)
    //******* 修改時請一併修改採購 明細
    public static function getPurchaseOverviewList(
          $purchase_sn = null
        , $title = null
        , $purchase_user_id = []
        , $purchase_sdate = null
        , $purchase_edate = null
        , $supplier_id = null
        , $depot_id = null
        , $inbound_user_id = []
        , $inbound_status = []
        , $inbound_sdate = null
        , $inbound_edate = null
        , $expire_day = null
    ) {
        //訂金單號
        $subColumn = DB::table('pcs_paying_orders as order')
            ->select('order.id')
            ->whereColumn('order.purchase_id', '=', 'purchase.id')
            ->where('order.type', '=', DB::raw('0'))
            ->whereNull('order.deleted_at')
            ->orderByDesc('order.id')
            ->limit(1);
        //尾款單號
        $subColumn2 = DB::table('pcs_paying_orders as order')
            ->select('order.id')
            ->whereColumn('order.purchase_id', '=', 'purchase.id')
            ->where('order.type', '=', DB::raw('1'))
            ->whereNull('order.deleted_at')
            ->orderByDesc('order.id')
            ->limit(1);

        $tempInboundSql = DB::table('pcs_purchase_inbound as inbound')
            ->select('event_id'
                , 'product_style_id')
            ->selectRaw('sum(inbound_num) as inbound_num')
            ->selectRaw('GROUP_CONCAT(DISTINCT inbound.inbound_user_id) as inbound_user_ids') //入庫人員
            ->selectRaw('GROUP_CONCAT(DISTINCT inbound.inbound_user_name) as inbound_user_names') //入庫人員
            ->where('event', Event::purchase()->value)
            ->whereNull('deleted_at')
            ->groupBy('event_id')
            ->groupBy('product_style_id');
        if ($depot_id) {
            $tempInboundSql->where('inbound.depot_id', '=', $depot_id);
        }
        if ($inbound_user_id) {
            $tempInboundSql->whereIn('inbound.inbound_user_id', $inbound_user_id);
        }
        if ($inbound_sdate && $inbound_edate) {
            $tempInboundSql->whereBetween('inbound.inbound_date', [date((string) $inbound_sdate), date((string) $inbound_edate)]);
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
                , 'inbound.inbound_num as inbound_num'
                , 'inbound.inbound_user_ids as inbound_user_ids'
                , 'inbound.inbound_user_names as inbound_user_names'
            )
            ->whereNull('items.deleted_at')
            ->orderBy('items.product_style_id')
            ->limit(1);
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
                ,'itemtb_new.inbound_user_ids as inbound_user_ids'
                ,'itemtb_new.inbound_user_names as inbound_user_names'
                ,'purchase.purchase_user_id as purchase_user_id'
                ,'purchase.supplier_id as supplier_id'
                ,'purchase.invoice_num as invoice_num'
                ,'purchase.purchase_user_name as purchase_user_name'
                ,'purchase.supplier_name as supplier_name'
                ,'purchase.supplier_nickname as supplier_nickname'
            )
            ->selectRaw('DATE_FORMAT(purchase.created_at,"%Y-%m-%d") as created_at')
            ->selectRaw('DATE_FORMAT(purchase.scheduled_date,"%Y-%m-%d") as scheduled_date')
            ->selectRaw('FORMAT(itemtb_new.price / itemtb_new.num, 2) as single_price')
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

        if($purchase_sn) {
            $result->where('purchase.sn', '=', $purchase_sn);
        }
        if ($purchase_user_id) {
            $result->whereIn('purchase.purchase_user_id', $purchase_user_id);
        }
        if ($purchase_sdate && $purchase_edate) {
            $result->whereBetween('purchase.created_at', [date((string) $purchase_sdate), date((string) $purchase_edate)]);
        }
        if ($supplier_id) {
            $result->where('purchase.supplier_id', '=', $supplier_id);
        }

        if ($inbound_user_id) {
            $result->whereIn('itemtb_new.inbound_user_ids', $inbound_user_id);
        }

        $result2 = DB::table(DB::raw("({$result->toSql()}) as tb"))
            ->select('*');

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
            ->leftJoin('pcs_paying_orders as pay_orders', 'pcs_items.purchase_id', '=', 'pay_orders.purchase_id')
            ->leftJoin('prd_product_styles as prd_styles', 'pcs_items.product_style_id', '=', 'prd_styles.id')
            ->leftJoin('prd_products', 'prd_styles.product_id', '=', 'prd_products.id')
            ->leftJoin('usr_users', 'prd_products.user_id', '=', 'usr_users.id')
            ->select(
                'pcs_items.title',
                'pcs_items.price as total_price',
                'pcs_items.num',
                'pcs_items.memo',
                'pcs_items.product_style_id as style_ids',
                'pay_orders.price as pay_order_price',
                'usr_users.name',
            )
            ->get()
            ->unique('style_ids');

        return $result;
    }
}
