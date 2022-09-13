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

    public static function getShipItem($sub_order_id)
    {
        $query_combo = DB::table('prd_style_combos')
            ->leftJoin('prd_product_styles', 'prd_product_styles.id', '=', 'prd_style_combos.product_style_child_id')
            ->select('prd_style_combos.product_style_id'
                , 'prd_style_combos.product_style_child_id'
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
                , DB::raw('@tb_combo.type:="c" as prd_type')
                , 'tb_combo.product_style_id AS papa_product_style_id'
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
                , 'prd_product_styles.type as prd_type'
                , DB::raw('null as papa_product_style_id')
                , 'prd_product_styles.id  AS product_style_id'
                , 'prd_product_styles.product_id'
                , 'prd_product_styles.sku'
            )
            ->selectRaw('ord_items.qty AS qty')
            ->selectRaw(DB::raw('Concat("") AS combo_product_title'));

        //組合時需要將欄位順序也對應好
        //一般商品的combo_product_title 設定為空字串
        $query_ship_overview = $query_ship->union($query_ship_combo);

        return $query_ship_overview;
    }

    public static function item_order($order_id)
    {
        $query = self::leftJoin('ord_orders', 'ord_orders.id', '=', 'ord_items.order_id')
            ->leftJoin('ord_sub_orders', 'ord_sub_orders.id', '=', 'ord_items.sub_order_id')
            ->leftJoin('prd_product_styles as styles', 'styles.id', '=', 'ord_items.product_style_id')
            ->leftJoin('prd_products as products', 'products.id', '=', 'styles.product_id')
            ->leftJoin('usr_users as users', 'users.id', '=', 'products.user_id')

            ->where([
                'ord_orders.id' => $order_id,
            ])
            ->select(
                'ord_orders.id AS order_id',
                'ord_orders.status AS order_status',
                'ord_orders.note AS order_note',

                'ord_sub_orders.sn AS del_sn',
                'ord_sub_orders.ship_category_name AS del_category_name',
                'ord_sub_orders.ship_event AS del_even',
                'ord_sub_orders.ship_temp AS del_temp',

                'ord_items.sku AS product_sku',
                'ord_items.product_title AS product_title',
                'ord_items.note AS product_note',
                'ord_items.price AS product_price',
                'ord_items.qty AS product_qty',
                'ord_items.origin_price AS product_origin_price',
                'ord_items.discount_value AS product_discount',
                'ord_items.discounted_price AS product_after_discounting_price',

                'products.id as product_id',
                'products.has_tax as product_taxation',

                'users.id as product_user_id',
                'users.name as product_user_name'
            );

        return $query;
    }

    public static function itemList($order_id, $options = [])
    {

        $re = DB::table('ord_items as item')
            ->leftJoin('ord_sub_orders as sub_order', 'item.sub_order_id', '=', 'sub_order.id')
            ->leftJoin('ord_orders as order', 'sub_order.order_id', '=', 'order.id')
            ->leftJoin('prd_sale_channels as channel', 'order.sale_channel_id', '=', 'channel.id')
            ->leftJoin('prd_product_styles as style', 'item.product_style_id', '=', 'style.id')
            ->leftJoin('prd_products as product', 'style.product_id', '=', 'product.id')
            ->leftJoin('usr_users as user', 'product.user_id', '=', 'user.id')
            ->select([
                'item.product_title',
                'item.discounted_price',
                'item.price',
                'item.dealer_price',
                'item.qty',
                'item.unit_cost',
                'item.origin_price',
                'user.name as product_user',
                'sub_order.sn as sub_order_sn',
                'channel.title as channel_title',
            ])
            ->where('item.order_id', $order_id);

        if (isset($options['profit'])) {
            $re->leftJoin('ord_order_profit as profit', function ($join) {
                $join->on('profit.sub_order_id', '=', 'item.sub_order_id')
                    ->on('profit.style_id', '=', 'item.product_style_id');
            })
                ->leftJoin('usr_customers as re_customer', 're_customer.id', '=', 'profit.customer_id')
                ->leftJoin('ord_order_profit as profit2', 'profit.id', '=', 'profit2.parent_id')
                ->leftJoin('usr_customers as re_customer2', 're_customer2.id', '=', 'profit2.customer_id')
                ->addSelect(['profit.id as profit_id',
                    'profit.total_bonus',
                    'profit.bonus',
                    're_customer.name as re_customer',
                    're_customer2.name as re_customer2',
                    'profit2.bonus as bonus2'])
                ->whereNull('profit.parent_id');

        }

        return $re;
    }

}
