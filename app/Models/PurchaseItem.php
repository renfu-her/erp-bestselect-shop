<?php

namespace App\Models;

use App\Helpers\IttmsUtils;
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
    public static function createPurchase($purchase_id, $product_style_id, $title, $sku, $price, $num, $temp_id = null, $memo = null)
    {
        return DB::transaction(function () use (
            $purchase_id,
            $product_style_id,
            $title,
            $sku,
            $price,
            $num,
            $temp_id,
            $memo
        ) {
            $id = self::create([
                "purchase_id" => $purchase_id,
                "product_style_id" => $product_style_id,
                "title" => $title,
                "sku" => $sku,
                "price" => $price,
                "num" => $num,
                "temp_id" => $temp_id,
                "memo" => $memo
            ])->id;

            return $id;
        });
    }


    public static function getData($purchase_id) {
        return self::where('purchase_id', $purchase_id)->whereNull('deleted_at');
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
            ->where('order.type', '=', '0')
            ->whereNull('order.deleted_at')
            ->orderByDesc('order.id')
            ->limit(1);
        //尾款單號
        $subColumn2 = DB::table('pcs_paying_orders as order')
            ->select('order.id')
            ->whereColumn('order.purchase_id', '=', 'purchase.id')
            ->where('order.type', '=', '1')
            ->whereNull('order.deleted_at')
            ->orderByDesc('order.id')
            ->limit(1);

        $tempInboundSql = DB::table('pcs_purchase_inbound as inbound')
            ->leftJoin('usr_users as user', 'user.id', '=', 'inbound.inbound_user_id')
            ->leftJoin('depot as depot', 'depot.id', '=', 'inbound.depot_id')
            ->select('inbound.id'
                , 'inbound.purchase_id'
                , 'inbound.status'
                , 'inbound.product_style_id'
                , 'user.name as inbound_user_name'
                , 'depot.name as depot_name')
            ->selectRaw('(inbound.inbound_date) as inbound_date')
            ->selectRaw('(inbound.inbound_num) as inbound_num')
            ->selectRaw('(inbound.error_num) as error_num')
            ->selectRaw('(inbound.sale_num) as sale_num')
            ->selectRaw('(inbound.expiry_date) as expiry_date')
            ->selectRaw('(inbound.depot_id) as depot_id')
            ->selectRaw('(inbound.inbound_user_id) as inbound_user_id')
            ->whereNull('inbound.deleted_at');
        if ($depot_id) {
            $tempInboundSql->where('inbound.depot_id', '=', $depot_id);
        }
        if ($inbound_user_id) {
            $tempInboundSql->whereIn('inbound.inbound_user_id', $inbound_user_id);
        }
        if ($inbound_status) {
            $tempInboundSql->whereIn('inbound.status', $inbound_status);
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

//        dd(IttmsUtils::getEloquentSqlWithBindings($tempInboundSql));
//        dd($result->get()->toArray());

        $result = DB::table('pcs_purchase as purchase')
            ->leftJoin('pcs_purchase_items as items', 'purchase.id', '=', 'items.purchase_id')
            ->leftJoinSub($tempInboundSql, 'inbound', function($join) use($tempInboundSql) {
                $join->on('items.purchase_id', '=', 'inbound.purchase_id')
                    ->on('items.product_style_id', '=', 'inbound.product_style_id');
            })
            ->leftJoin('usr_users as user', 'user.id', '=', 'purchase.purchase_user_id')
            ->leftJoin('prd_suppliers as supplier', 'supplier.id', '=', 'purchase.supplier_id')
            //->select('*')
            ->select('purchase.id as id'
                ,'purchase.sn as sn'
                ,'items.id as items_id'
                ,'items.title as title'
                ,'purchase.created_at as created_at'
                ,'items.sku as sku'
                ,'items.price as price'
                ,'items.num as num'
                ,'purchase.purchase_user_id as purchase_user_id'
                ,'purchase.supplier_id as supplier_id'
                ,'purchase.invoice_num as invoice_num'
                ,'user.name as purchase_user_name'
                ,'supplier.name as supplier_name'
                ,'supplier.nickname as supplier_nickname'

                ,'inbound.id as inbound_id'
                ,'inbound.status as inbound_status'
                ,'inbound.inbound_num as inbound_num'
                ,'inbound.error_num as error_num'
                ,'inbound.sale_num as sale_num'
                ,'inbound.depot_id as depot_id'
                ,'inbound.inbound_user_id as inbound_user_id'
                ,'inbound.inbound_user_name as inbound_user_name'
                ,'inbound.depot_name as depot_name'
            )
            ->selectRaw('DATE_FORMAT(purchase.scheduled_date,"%Y-%m-%d") as scheduled_date')
            ->selectRaw('DATE_FORMAT(inbound.inbound_date,"%Y-%m-%d") as inbound_date')
            ->selectRaw('DATE_FORMAT(inbound.expiry_date,"%Y-%m-%d") as expiry_date')
            ->selectRaw('items.price * items.num as total_price')
//            ->selectRaw('('. $caseInboundSql. ') as inbound_status')
            ->addSelect(['deposit_num' => $subColumn, 'final_pay_num' => $subColumn2])
            ->whereNull('purchase.deleted_at')
            ->whereNull('items.deleted_at')
            ->whereNull('user.deleted_at')
            ->whereNull('supplier.deleted_at')
//            ->whereNotNull('inbound.inbound_num')
            ->orderBy('id')
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
//        if($sku) {
//            $result->where(function ($query) use ($sku) {
//                $query->Where('items.sku', 'like', "%{$sku}%");
//            });
//        }
        if ($purchase_user_id) {
            $result->whereIn('purchase.purchase_user_id', $purchase_user_id);
        }
        if ($purchase_sdate && $purchase_edate) {
            $result->whereBetween('purchase.scheduled_date', [date((string) $purchase_sdate), date((string) $purchase_edate)]);
        }
        if ($supplier_id) {
            $result->where('purchase.supplier_id', '=', $supplier_id);
        }
//        dd(IttmsUtils::getEloquentSqlWithBindings($result));
//        dd($result->get()->toArray());
        return $result;
    }

    //採購 總表
    //******* 修改時請一併修改採購 明細
    public static function getPurchaseOverviewList(
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
            ->where('order.type', '=', '0')
            ->whereNull('order.deleted_at')
            ->orderByDesc('order.id')
            ->limit(1);
        //尾款單號
        $subColumn2 = DB::table('pcs_paying_orders as order')
            ->select('order.id')
            ->whereColumn('order.purchase_id', '=', 'purchase.id')
            ->where('order.type', '=', '1')
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
        if ($inbound_status) {
            $tempInboundSql->whereIn('inbound.status', $inbound_status);
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
//        if($sku) {
//            $tempPurchaseItemSql->where(function ($query) use ($sku) {
//                $query->Where('items.sku', 'like', "%{$sku}%");
//            });
//        }

//        dd(IttmsUtils::getEloquentSqlWithBindings($tempInboundSql));
//        dd($result->get()->toArray());

        $result = DB::table('pcs_purchase as purchase')
            ->leftJoinSub($tempPurchaseItemSql, 'itemtb_new', function($join) use($tempPurchaseItemSql) {
                $join->on('itemtb_new.purchase_id', '=', 'purchase.id');
            })
            ->leftJoin('usr_users as user', 'user.id', '=', 'purchase.purchase_user_id')
            ->leftJoin('prd_suppliers as supplier', 'supplier.id', '=', 'purchase.supplier_id')
            //->select('*')
            ->select('purchase.id as id'
                ,'purchase.sn as sn'
                ,'itemtb_new.id as items_id'
                ,'itemtb_new.title as title'
                ,'purchase.created_at as created_at'
                ,'itemtb_new.sku as sku'
                ,'itemtb_new.price as price'
                ,'itemtb_new.num as num'
                ,'purchase.purchase_user_id as purchase_user_id'
                ,'purchase.supplier_id as supplier_id'
                ,'purchase.invoice_num as invoice_num'
                ,'user.name as purchase_user_name'
                ,'supplier.name as supplier_name'
                ,'supplier.nickname as supplier_nickname'
            )
            ->selectRaw('DATE_FORMAT(purchase.scheduled_date,"%Y-%m-%d") as scheduled_date')
            ->selectRaw('itemtb_new.price * itemtb_new.num as total_price')
            ->addSelect(['deposit_num' => $subColumn, 'final_pay_num' => $subColumn2])
            ->whereNull('purchase.deleted_at')
            ->whereNull('user.deleted_at')
            ->whereNull('supplier.deleted_at')
            ->orderBy('purchase.id');

        if($purchase_sn) {
            $result->where('purchase.sn', '=', $purchase_sn);
        }
        if ($purchase_user_id) {
            $result->whereIn('purchase.purchase_user_id', $purchase_user_id);
        }
        if ($purchase_sdate && $purchase_edate) {
            $result->whereBetween('purchase.scheduled_date', [date((string) $purchase_sdate), date((string) $purchase_edate)]);
        }
        if ($supplier_id) {
            $result->where('purchase.supplier_id', '=', $supplier_id);
        }
//        dd(IttmsUtils::getEloquentSqlWithBindings($result));
//        dd($result->get()->toArray());
        return $result;
    }
}
