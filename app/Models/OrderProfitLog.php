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

    public static function createLog($order_id, $sub_order_sn, $product_title, $order_item_id, $bonus1, $bonus2, $user_id, $customer1, $customer2)
    {
        self::create([
            'order_id' => $order_id,
            'sub_order_sn' => $sub_order_sn,
            'product_title' => $product_title,
            'bonus1' => $bonus1,
            'bonus2' => $bonus2,
            'exec_user_id' => $user_id,
            'customer_id1' => $customer1,
            'customer_id2' => $customer2,
            'order_item_id' => $order_item_id,
        ]);
    }

    public static function dataList($order_id)
    {
        return DB::table('ord_order_profit_log as log')
            ->join('usr_users as user', 'user.id', '=', 'log.exec_user_id')
            ->join('usr_customers as customer1', 'customer1.id', '=', 'log.customer_id1')
            ->leftJoin('usr_customers as customer2', 'customer2.id', '=', 'log.customer_id2')
            ->select(['log.bonus1',
                'log.bonus2',
                'log.created_at',
                'log.sub_order_sn',
                'user.name',
                'log.product_title',
                'customer1.name as customer1',
                'customer2.name as customer2'])
            ->where('log.order_id', $order_id);
    }

    public static function dataListPerson($order_id, $customer_id)
    {
        return DB::table('ord_order_profit_log as log')
            ->join('usr_users as user', 'user.id', '=', 'log.exec_user_id')
            ->where('log.order_id', $order_id)
            ->where(function ($query) use ($customer_id) {
                $query->where('log.customer_id1', $customer_id)
                    ->orWhere('log.customer_id2', $customer_id);
            })
            ->select(['log.*', 'user.name'])
            ->selectRaw("IF(log.customer_id1 = '$customer_id',log.bonus1,log.bonus2) AS bonus")
            ->orderBy('log.created_at', 'DESC');

    }

}
