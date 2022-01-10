<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PurchaseInbound extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pcs_purchase_inbound';
    protected $guarded = [];

    public static function createInbound($purchase_id, $product_style_id, $expiry_date = null, $status = 0, $inbound_date = null, $inbound_num = 0, $error_num = 0, $depot_id = null, $inbound_user_id = null, $memo = null)
    {
        $id = self::create([
            "purchase_id" => $purchase_id,
            "product_style_id" => $product_style_id,
            "expiry_date" => $expiry_date,
            "status" => $status,
            "inbound_date" => $inbound_date,
            "inbound_num" => $inbound_num,
            "error_num" => $error_num,
            "depot_id" => $depot_id,
            "inbound_user_id" => $inbound_user_id,
            "memo" => $memo
        ])->id;

        return $id;
    }

    //入庫 更新資料
    public static function updateInbound($id, $expiry_date = null, $status = 0, $inbound_date = null, $inbound_num = 0, $error_num = 0, $depot_id = null, $inbound_user_id = null, $close_date = null, $sale_num = 0, $memo = null)
    {
        return DB::transaction(function () use (
            $id,
            $expiry_date,
            $status,
            $inbound_date,
            $inbound_num,
            $error_num,
            $depot_id,
            $inbound_user_id,
            $close_date,
            $sale_num,
            $memo
        ) {
            self::where('id', '=', $id)->update([
                'expiry_date' => $expiry_date,
                'status' => $status,
                'inbound_date' => $inbound_date,
                'inbound_num' => $inbound_num,
                'error_num' => $error_num,
                'depot_id' => $depot_id,
                'inbound_user_id' => $inbound_user_id,
                'close_date' => $close_date,
                'sale_num' => $sale_num,
                'memo' => $memo

            ]);

            return $id;
        });
    }
    //售出 更新資料
    public static function sellInbound($id, $sale_num = 0)
    {
        return DB::transaction(function () use (
            $id,
            $sale_num
        ) {
            self::where('id', '=', $id)->update([
                'sale_num' => DB::raw('sale_num + '. $sale_num ),
            ]);

            return $id;
        });
    }

    //歷史入庫
    public static function getInboundList($purchase_id)
    {
        $result = DB::table('pcs_purchase as purchase')
            ->leftJoin('pcs_purchase_items as items', 'items.purchase_id', '=', 'purchase.id')
            ->leftJoin('pcs_purchase_inbound as inbound', 'inbound.product_style_id', '=', 'items.product_style_id')
            ->leftJoin('usr_users as users', 'users.id', '=', 'inbound.inbound_user_id')
            //->select('*')
            ->select('purchase.id as purchase_id'
                , 'items.title as title'
                , 'items.sku as sku'
                , 'items.num as item_num'
                , 'items.memo as item_memo'
                , 'inbound.id as inbound_id'
                , 'inbound.inbound_date as inbound_date'
                , 'inbound.expiry_date as expiry_date'
                , 'inbound.status as status'
                , 'inbound.inbound_date as inbound_date'
                , 'inbound.inbound_num as inbound_num'
                , 'inbound.error_num as error_num'
                , 'inbound.depot_id as depot_id'
                , 'inbound.inbound_user_id as inbound_user_id'
                , 'inbound.close_date as close_date'
                , 'inbound.memo as inbound_memo'
                , 'users.name as user_name'
            )
            ->whereNull('purchase.deleted_at')
            ->whereNull('items.deleted_at')
            ->whereNull('inbound.deleted_at')
            ->whereNotNull('inbound.id')
            ->where('purchase.id', '=', $purchase_id);
        return $result;
    }

    //採購單入庫總覽
    public static function getOverviewInboundList($purchase_id)
    {
        $tempInboundSql = DB::table('pcs_purchase_inbound as inbound')
            ->where('inbound.purchase_id', '=', $purchase_id)
            ->whereNull('inbound.deleted_at')
            ->select('inbound.purchase_id as purchase_id'
                , 'inbound.product_style_id as product_style_id')
            ->selectRaw('sum(inbound.inbound_num) as inbound_num')
            ->selectRaw('sum(inbound.error_num) as error_num')
            ->groupBy('inbound.purchase_id')
            ->groupBy('inbound.product_style_id');

        $result = DB::table('pcs_purchase as purchase')
            ->leftJoin('pcs_purchase_items as items', 'items.purchase_id', '=', 'purchase.id')
//            ->leftJoin('pcs_purchase_inbound as inbound', 'inbound.product_style_id', '=', 'items.product_style_id')
            ->leftJoin(DB::raw("({$tempInboundSql->toSql()}) as inbound"), function ($join) {
                $join->on('inbound.purchase_id', '=', 'items.purchase_id');
                $join->on('inbound.product_style_id', '=', 'items.product_style_id');
            })
            ->leftJoin('prd_product_styles as styles', 'styles.id', '=', 'items.product_style_id')
            ->leftJoin('prd_products as products', 'products.id', '=', 'styles.product_id')
            ->leftJoin('usr_users as users', 'users.id', '=', 'products.user_id')
            ->select('purchase.id as purchase_id'
                , 'items.product_style_id as product_style_id'
                , 'products.title as product_title'
                , 'styles.title as style_title'
                , 'users.name as user_name'
            )
            ->selectRaw('any_value(items.sku) as sku')
            ->selectRaw('sum(items.num) as num')
            ->selectRaw('(inbound.inbound_num) as inbound_num')
            ->selectRaw('(inbound.error_num) as error_num')
            ->selectRaw('( COALESCE(sum(items.num), 0) - COALESCE((inbound.inbound_num), 0) ) AS sould_enter_num')
            ->whereNull('purchase.deleted_at')
            ->whereNull('items.deleted_at')
            ->where('purchase.id', '=', $purchase_id)
            ->groupBy('purchase.id', 'items.product_style_id')
            ->orderBy('purchase.id')
            ->orderBy('items.product_style_id')
            ->mergeBindings($tempInboundSql);
        return $result;
    }
}
