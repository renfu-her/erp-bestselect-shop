<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderProfit extends Model
{
    use HasFactory;
    protected $table = 'ord_order_profit';
    protected $guarded = [];

    public static function dataList($order_id = null, $customer_id = null, $withParent = null)
    {

        $re = DB::table('ord_order_profit as profit')
            ->join('ord_items as item', function ($join) {
                $join->on('profit.sub_order_id', '=', 'item.sub_order_id')
                    ->on('profit.style_id', '=', 'item.product_style_id');
            })
            ->leftJoin('prd_product_styles as style', 'profit.style_id', '=', 'style.id')
            ->leftJoin('prd_products as product', 'style.product_id', '=', 'product.id')
            ->leftJoin('usr_users as user', 'product.user_id', '=', 'user.id')
            ->select([
                'profit.id',
                'profit.order_id',
                'profit.order_sn',
                'profit.sub_order_id',
                'profit.sub_order_sn',
                'profit.total_bonus',
                'item.product_title',
                'item.discounted_price',
                'profit.bonus',
                'item.price',
                'item.qty',
                'item.origin_price',
                'user.name as product_user',
            ]);

        if ($customer_id) {
            $re->where('profit.customer_id', $customer_id);
        }

        if ($order_id) {
            $re->where('profit.order_id', $order_id);
        }

        if ($withParent) {

            $re->whereNull('profit.parent_id')
                ->leftJoin('ord_order_profit as profit2', 'profit.id', '=', 'profit2.parent_id')
                ->leftJoin('usr_customers as re_customer', 're_customer.id', '=', 'profit2.customer_id')
                ->addSelect(['re_customer.name as re_customer', 'profit2.bonus as bonus2']);

        }

        return $re;
    }

    public static function updateProfit($profit_id, $bonus1, $bonus2)
    {
        DB::beginTransaction();

        $profit = self::where('id', $profit_id)->get()->first();

        if ($bonus1 + $bonus2 > $profit->total_bonus) {
            return ['success' => '0',
                'message' => '金額超出上限'];
        }


        self::where('id', $profit_id)->update(['bonus' => $bonus1]);

        self::where('parent_id', $profit_id)->update(['bonus' => $bonus2]);

        DB::commit();

        return ['success'=>'1'];
        
    }

}
