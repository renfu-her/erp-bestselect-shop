<?php

namespace App\Models;

use App\Enums\Purchase\InboundStatus;
use App\Enums\Purchase\LogFeature;
use App\Enums\Purchase\LogFeatureEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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
                    "memo" => $newData['memo']
                ])->id;

                PurchaseLog::stockChange($id, $newData['product_style_id'], LogFeature::style()->value, $id, LogFeatureEvent::style_add()->value, $newData['num'], null, $operator_user_id, $operator_user_name);
                return $id;
            });
        } else {
            return null;
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
                    if ($dirtykey == 'price') {
                        $event = '修改價錢';
                    } else if($dirtykey == 'num') {
                        $event = '修改數量';
                    } else if($dirtykey == 'memo') {
                        $event = '修改備註';
                    }
                    PurchaseLog::stockChange($purchaseItem->purchase_id, $purchaseItem->product_style_id, LogFeature::style()->value, $itemId, LogFeatureEvent::style_change_data()->value, $dirtyval, $event, $operator_user_id, $operator_user_name);
                }
                PurchaseItem::where('id', $itemId)->update([
                    "price" => $purchaseItemReq['price'][$key],
                    "num" => $purchaseItemReq['num'][$key],
                    "memo" => $purchaseItemReq['memo'][$key],
                ]);
            }
            return $changeStr;
        });
    }

    public static function deleteItems(array $del_item_id_arr, $operator_user_id, $operator_user_name) {
        if (0 < count($del_item_id_arr)) {
            return DB::transaction(function () use ($del_item_id_arr, $operator_user_id, $operator_user_name
            ) {
                PurchaseItem::whereIn('id', $del_item_id_arr)->delete();
                foreach ($del_item_id_arr as $del_id) {
                    PurchaseLog::stockChange($del_id, null, LogFeature::style()->value, $del_id, LogFeatureEvent::style_del()->value, null, null, $operator_user_id, $operator_user_name);
                }
            });
        }
    }

    //更新到貨數量
    public static function updateArrivedNum($id, $addnum) {
        return DB::transaction(function () use ($id, $addnum
        ) {
            PurchaseItem::where('id', $id)
                ->update(['arrived_num' => DB::raw("arrived_num + $addnum")]);
        });
    }

    public static function getData($purchase_id) {
        return self::where('purchase_id', $purchase_id)->whereNull('deleted_at');
    }

    public static function getDataForInbound($purchase_id) {
        $result = DB::table('pcs_purchase_items as items')
            ->select('items.id'
                , 'items.purchase_id'
                , 'items.product_style_id'
                , 'items.title'
                , 'items.sku'
                , 'items.num'
                , 'items.arrived_num'
            )
            ->selectRaw('( COALESCE(items.num, 0) - COALESCE(items.arrived_num, 0) ) as should_enter_num')
            ->where('purchase_id', $purchase_id)->whereNull('deleted_at');

        return $result;
    }

    //採購 明細
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
            ->select('purchase_id'
                , 'product_style_id')
            ->selectRaw('sum(inbound_num) as inbound_num')
            ->whereNull('deleted_at')
            ->groupBy('purchase_id')
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
                $join->on('items.purchase_id', '=', 'inbound.purchase_id')
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
            ->selectRaw('(case
                    when '. $query_not_yet. ' then "'. InboundStatus::getDescription(InboundStatus::not_yet()->value). '"
                    when '. $query_normal. ' then "'. InboundStatus::getDescription(InboundStatus::normal()->value). '"
                    when '. $query_shortage. ' then "'. InboundStatus::getDescription(InboundStatus::shortage()->value). '"
                    when '. $query_overflow. ' then "'. InboundStatus::getDescription(InboundStatus::overflow()->value). '"
                end) as inbound_status')
            ->addSelect(['deposit_num' => $subColumn, 'final_pay_num' => $subColumn2])
            ->whereNull('purchase.deleted_at')
            ->whereNull('items.deleted_at')
            ->orderByDesc('purchase.created_at')
            ->orderBy('items_id');

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
            ->select('*');

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
        $result->mergeBindings($subColumn);
        $result->mergeBindings($subColumn2);
        $result2->mergeBindings($result);
        return $result2;
    }

    //採購 總表
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
            ->select('purchase_id'
                , 'product_style_id')
            ->selectRaw('sum(inbound_num) as inbound_num')
            ->whereNull('deleted_at')
            ->groupBy('purchase_id')
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
                $join->on('items.purchase_id', '=', 'inbound.purchase_id')
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
            ->selectRaw('(case
                    when '. $query_not_yet. ' then "'. InboundStatus::getDescription(InboundStatus::not_yet()->value). '"
                    when '. $query_normal. ' then "'. InboundStatus::getDescription(InboundStatus::normal()->value). '"
                    when '. $query_shortage. ' then "'. InboundStatus::getDescription(InboundStatus::shortage()->value). '"
                    when '. $query_overflow. ' then "'. InboundStatus::getDescription(InboundStatus::overflow()->value). '"
                end) as inbound_status')
            ->addSelect(['deposit_num' => $subColumn, 'final_pay_num' => $subColumn2])
            ->whereNull('purchase.deleted_at')
            ->orderByDesc('purchase.created_at');

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
        $result2 = DB::table(DB::raw("({$result->toSql()}) as tb"))
            ->select('*');

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
        $result->mergeBindings($subColumn);
        $result->mergeBindings($subColumn2);
        $result2->mergeBindings($result);
        return $result2;
    }
}
