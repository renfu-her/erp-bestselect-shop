<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderProfitLog extends Model
{
    use HasFactory;
    protected $table = 'ord_order_profit_log';
    protected $guarded = [];

    public static function createLog($profit_id, $order_id, $bonus1, $bonus2, $user_id, $customer1, $customer2)
    {
        self::create([
            'profit_id' => $profit_id,
            'bonus1' => $bonus1,
            'bonus2' => $bonus2,
            'exec_user_id' => $user_id,
            'order_id' => $order_id,
            'customer_id1' => $customer1,
            'customer_id2' => $customer2,
        ]);
    }

    public static function dataList($order_id)
    {
        return DB::table('ord_order_profit as profit')
            ->join('ord_order_profit_log as log', 'profit.id', '=', 'log.profit_id')
            ->join('usr_users as user', 'user.id', '=', 'log.exec_user_id')
            ->join('ord_items as item', function ($join) {
                $join->on('profit.sub_order_id', '=', 'item.sub_order_id')
                    ->on('profit.style_id', '=', 'item.product_style_id');
            })
            ->join('usr_customers as customer1', 'customer1.id', '=', 'log.customer_id1')
            ->leftJoin('usr_customers as customer2', 'customer2.id', '=', 'log.customer_id2')
            ->select(['log.bonus1',
                'log.bonus2',
                'log.created_at',
                'profit.sub_order_sn',
                'user.name',
                'item.product_title',
                'customer1.name as customer1',
                'customer2.name as customer2'])
            ->where('profit.order_id', $order_id);
    }

    public static function dataListPerson($order_id, $customer_id)
    {
        return DB::table('ord_order_profit_log as log')
            ->join('ord_order_profit as profit as profit', 'profit.order_id', '=', 'log.order_id')
            ->join('usr_users as user', 'user.id', '=', 'log.exec_user_id')

            ->join('ord_items as item', function ($join) {
                $join->on('profit.sub_order_id', '=', 'item.sub_order_id')
                    ->on('profit.style_id', '=', 'item.product_style_id');
            })
            ->where('log.order_id', $order_id)
            ->where('profit.customer_id', $customer_id)
            ->select(['profit.parent_id', 'profit.sub_order_sn', 'log.*', 'user.name', 'item.product_title'])
            ->selectRaw('IF(profit.parent_id IS NULL,log.bonus1,log.bonus2) AS bonus')
            ->orderBy('log.created_at', 'DESC');

    }

}
