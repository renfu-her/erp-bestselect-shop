<?php

namespace App\Models;

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

    public static function getPurchaseDetailList($id = null
        , $purchase_user_id = []
        , $inbound_user_id = []
        , $inbound_status = null
        , $depot_id = null
        , $purchase_sdate = null
        , $purchase_edate = null
        , $inbound_sdate = null
        , $inbound_edate = null
        , $expiry_date = null
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

        $inbound_tb = DB::table('pcs_purchase_inbound as inbound')
            ->select('*')
            ->whereColumn('order.purchase_id', '=', 'purchase.id')
            ->where('order.type', '=', '1')
            ->whereNull('order.deleted_at')
            ->orderByDesc('order.id')
            ->limit(1);

        $tempInboundSql = 'select sum(pcs_purchase_inbound.inbound_num) as inbound_num, purchase_id, product_style_id from pcs_purchase_inbound where deleted_at is null group by purchase_id, product_style_id';

		//入庫 有入庫紀錄且結單日期為空
		//已結單 結單日期有值
        //新增 尚未入庫且結單日期為空
        $caseInboundSql = 'case
                when `items`.`id` is not null and inbound.inbound_num is not null and `purchase`.`close_date` is null then \'入庫\'
                when `purchase`.`close_date` is not null then \'已結單\'
                else \'新增\'
            end';

        $result = DB::table('pcs_purchase as purchase')
            ->leftJoin('pcs_purchase_items as items', 'purchase.id', '=', 'items.purchase_id')
            ->leftJoin(DB::raw('(' . $tempInboundSql . ') as inbound'), function($join) {
                $join->on('items.purchase_id', '=', 'inbound.purchase_id');
                $join->on('items.product_style_id', '=', 'inbound.product_style_id');
            })
            ->leftJoin('usr_users as user', 'user.id', '=', 'purchase.purchase_user_id')
            ->leftJoin('prd_suppliers as supplier', 'supplier.id', '=', 'purchase.supplier_id')
            //->select('*')
            ->select('purchase.id as id'
                ,'purchase.sn as sn'
                ,'items.id as items_id'
                ,'items.title as title'
                ,'purchase.created_at as created_at'
                ,'purchase.scheduled_date as scheduled_date'
                ,'items.sku as sku'
                ,'items.price as price'
                ,'items.num as num'
                ,'purchase.purchase_user_id as purchase_user_id'
                ,'purchase.supplier_id as supplier_id'
                ,'purchase.invoice_num as invoice_num'
                ,'user.name as user_name'
                ,'supplier.name as supplier_name'
                ,'supplier.nickname as supplier_nickname'
            )
            ->selectRaw('items.price * items.num as total_price')
            ->selectRaw('('. $caseInboundSql. ') as inbound_status')
            ->addSelect(['deposit_num' => $subColumn, 'final_pay_num' => $subColumn2])
            ->whereNull('purchase.deleted_at')
            ->whereNull('items.deleted_at')
            ->whereNull('user.deleted_at')
            ->whereNull('supplier.deleted_at')
            ->orderBy('id')
            ->orderBy('items_id');
//        $result = DB::table(DB::raw("({$result->toSql()}) as sub"))
//            ->select('sub.*')
//            ->groupBy('sub.id')
//            ->mergeBindings($result);


//        if ($purchase_sdate && $purchase_edate) {
//            $result->whereBetween('purchase.created_at', [date((string) $purchase_sdate), date((string) $purchase_edate)]);
//        }
//        dd(IttmsUtils::getEloquentSqlWithBindings($result));
//        dd($result->get()->toArray());
        return $result;
    }
}
