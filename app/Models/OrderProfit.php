<?php

namespace App\Models;

use App\Enums\Customer\ProfitStatus;
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
        /*
        $re->whereNull('profit.parent_id')
        ->leftJoin('ord_order_profit as profit2', 'profit.id', '=', 'profit2.parent_id')
        ->leftJoin('usr_customers as re_customer', 're_customer.id', '=', 'profit2.customer_id')
        ->addSelect(['re_customer.name as re_customer', 'profit2.bonus as bonus2']);
         */
        return $re;
    }

    public static function updateProfit($profit_id, $bonus1, $bonus2, $user_id)
    {
        DB::beginTransaction();

        $profit = self::where('id', $profit_id)->get()->first();

        if ($bonus1 + $bonus2 > $profit->total_bonus) {
            return ['success' => '0',
                'message' => '金額超出上限'];
        }

        self::where('id', $profit_id)->update(['bonus' => $bonus1]);
        $parentProfit = self::where('parent_id', $profit_id);
        $customer_id2 = null;

        if ($parentProfit->get()->first()) {
            $parentProfit->update(['bonus' => $bonus2]);
            $customer_id2 = $parentProfit->get()->first()->customer_id;
        }

        $item = OrderItem::where('sub_order_id', $profit->sub_order_id)
            ->where('product_style_id', $profit->style_id)->get()->first();

        OrderProfitLog::createLog($profit->order_id,
            $profit->sub_order_sn,
            $item->product_title,
            $item->id,
            $bonus1,
            $bonus2,
            $user_id,
            $profit->customer_id,
            $customer_id2);

        DB::commit();

        return ['success' => '1'];

    }

    public static function changeOwner($order_id, $customer_id, $exec_user_id)
    {
        DB::beginTransaction();

        $order = Order::where('id', $order_id)->get()->first();
        self::where('order_id', $order_id)->delete();

        //確認資格
        $customerProfit = CustomerProfit::getProfitData($customer_id, ProfitStatus::Success());

        if (!$customerProfit) {
            DB::rollBack();
            return ['success' => '0', 'message' => '無分潤資格'];
        }
        //上一代分潤資格
        $parentCustomerProfit = null;

        if ($customerProfit->parent_cusotmer_id) {
            $parentCustomerProfit = CustomerProfit::getProfitData($customerProfit->parent_cusotmer_id, ProfitStatus::Success());
        }

        $profit_rate = 100;

        if ($parentCustomerProfit) {
            $profit_rate = $customerProfit->profit_rate;
        }

        $items = DB::table('ord_sub_orders as sub_order')
            ->join('ord_items as item', 'sub_order.id', '=', 'item.sub_order_id')
            ->where('item.order_id', $order_id)
            ->select(['sub_order.sn as sub_order_sn', 'item.*', 'item.id as order_item_id'])->get();

        foreach ($items as $item) {
            $bonus = $item->bonus * $item->qty;
            // dd($bonus);
            if ($profit_rate != 100) {
                $cBonus = floor($bonus / 100 * $profit_rate);
            } else {
                $cBonus = $bonus;
            }
            $pBonus = $bonus - $cBonus;

            $updateData = ['order_id' => $order_id,
                'order_sn' => $order->sn,
                'sub_order_sn' => $item->sub_order_sn,
                'sub_order_id' => $item->sub_order_id,
                'style_id' => $item->product_style_id,
                'total_bonus' => $bonus];
            //    dd($updateData);

            $pid = OrderProfit::create(array_merge($updateData, [
                'bonus' => $cBonus,
                'customer_id' => $customer_id,
            ]))->id;

            if ($parentCustomerProfit && $pBonus) {
                OrderProfit::create(array_merge($updateData, [
                    'bonus' => $pBonus,
                    'customer_id' => $customerProfit->parent_cusotmer_id,
                    'parent_id' => $pid,
                ]));
              
            }

            OrderProfitLog::createLog($order_id,
                $item->sub_order_sn,
                $item->product_title,
                $item->id,
                $cBonus,
                $pBonus,
                $exec_user_id,
                $customer_id,
                $customerProfit->parent_cusotmer_id);

        }

        DB::commit();

        return ['success' => '1'];

    }

}
