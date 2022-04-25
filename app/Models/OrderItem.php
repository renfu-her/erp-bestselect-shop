<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'ord_items';
    public $timestamps = false;
    protected $guarded = [];

    public static function getShipItem($sub_order_id) {
        $query_combo = DB::table('prd_style_combos')
            ->leftJoin('prd_product_styles', 'prd_product_styles.id', '=', 'prd_style_combos.product_style_child_id')
            ->select('prd_style_combos.product_style_id'
                , 'prd_style_combos.qty'
                , 'prd_product_styles.id'
                , 'prd_product_styles.product_id'
                , 'prd_product_styles.title'
                , 'prd_product_styles.sku'
                , 'prd_product_styles.type'
            );

        //取得子訂單商品內 組合包拆解內容
        $query_ship_combo = DB::table('ord_items')
            ->leftJoin(DB::raw("({$query_combo->toSql()}) as tb_combo"), function ($join) {
                $join->on('tb_combo.product_style_id', '=', 'ord_items.product_style_id');
            })
            ->leftJoin('prd_products', 'prd_products.id', '=', 'tb_combo.product_id')
            ->select('ord_items.id AS item_id'
                , 'ord_items.order_id AS order_id'
                , 'ord_items.sub_order_id AS sub_order_id'
                , 'ord_items.product_title AS combo_product_title'
                , 'tb_combo.id AS product_style_id'
                , 'tb_combo.product_id'
                , 'tb_combo.sku'
            )
            ->selectRaw(DB::raw('( ord_items.qty * tb_combo.qty ) AS qty'))
            ->selectRaw(DB::raw('Concat(prd_products.title, "-", tb_combo.title) AS product_title'))
            ->whereNotNull('tb_combo.type')
            ->where('ord_items.sub_order_id', '=', $sub_order_id)
            ->mergeBindings($query_combo);

        //取得子訂單商品內 一般商品
        $query_ship = DB::table('ord_items')
            ->leftJoin('prd_product_styles', 'prd_product_styles.id', '=', 'ord_items.product_style_id')
            ->where('prd_product_styles.type', '=', 'p')
            ->where('ord_items.sub_order_id', '=', $sub_order_id)
            ->select('ord_items.id AS item_id'
                , 'ord_items.order_id AS order_id'
                , 'ord_items.sub_order_id AS sub_order_id'
                , 'ord_items.product_title'
                , 'prd_product_styles.id  AS product_style_id'
                , 'prd_product_styles.product_id'
                , 'prd_product_styles.sku'
            )
            ->selectRaw('ord_items.qty AS qty')
            ->selectRaw(DB::raw('Concat("") AS combo_product_title'));

        //組合時需要將欄位順序也對應好
        //一般商品的combo_product_title 設定為空字串
        $query_ship_overview = $query_ship->union($query_ship_combo);

        $result = DB::table( DB::raw("({$query_ship_overview->toSql()}) as tb_ship") )
            ->leftJoin('dlv_receive_depot', 'dlv_receive_depot.event_item_id', '=', 'tb_ship.item_id')
            ->mergeBindings($query_ship_overview)
            ->select('tb_ship.item_id'
                , 'tb_ship.order_id'
                , 'tb_ship.sub_order_id'
                , 'tb_ship.qty as total_qty'
                , 'tb_ship.combo_product_title'
                , 'tb_ship.product_title'
                , 'tb_ship.product_style_id'
                , 'tb_ship.product_id'
                , 'tb_ship.sku'
//                , 'tb_ship.type'
                , 'dlv_receive_depot.id'
                , 'dlv_receive_depot.delivery_id'
                , 'dlv_receive_depot.freebies'
                , 'dlv_receive_depot.inbound_id'
                , 'dlv_receive_depot.inbound_sn'
                , 'dlv_receive_depot.depot_id'
                , 'dlv_receive_depot.depot_name'
                , 'dlv_receive_depot.product_style_id'
                , 'dlv_receive_depot.sku'
                , 'dlv_receive_depot.product_title'
                , 'dlv_receive_depot.qty'
                , 'dlv_receive_depot.expiry_date'
                , 'dlv_receive_depot.audit_date'
                , 'dlv_receive_depot.deleted_at')
            ->whereNull('dlv_receive_depot.deleted_at')
            ->where('tb_ship.sub_order_id', '=', $sub_order_id)
            ->orderBy('tb_ship.item_id')
            ->orderBy('dlv_receive_depot.id');

        return $query_ship_overview;
    }


    public static function item_order($order_id)
    {
        $query = self::leftJoin('ord_orders', 'ord_orders.id', '=', 'ord_items.order_id')
            ->leftJoin('ord_sub_orders', 'ord_sub_orders.id', '=', 'ord_items.sub_order_id')
            ->where([
                'ord_orders.id'=>$order_id,
            ])
            ->select(
                'ord_orders.id AS order_id',
                'ord_orders.status AS order_status',

                'ord_sub_orders.sn AS del_sn',
                'ord_sub_orders.ship_category_name AS del_category_name',
                'ord_sub_orders.ship_event AS del_even',
                'ord_sub_orders.ship_temp AS del_temp',

                'ord_items.sku AS product_sku',
                'ord_items.product_title AS product_title',
                'ord_items.price AS product_price',
                'ord_items.qty AS product_qty',
                'ord_items.origin_price AS product_origin_price',
                'ord_items.discount_value AS product_discount',
                'ord_items.discounted_price AS product_after_discounting_price',
            );

        return $query;
    }
}
