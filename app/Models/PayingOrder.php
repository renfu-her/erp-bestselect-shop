<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PayingOrder extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'pcs_paying_orders';
    protected $guarded = [];

    public static function createPayingOrder(
        $purchase_id,
        $usr_users_id,
        $type,
        $price = null,
        $pay_date = null,
        $memo = null
    ) {
        return DB::transaction(function () use (
            $purchase_id,
            $usr_users_id,
            $type,
            $price,
            $pay_date,
            $memo
        ) {
            $sn = "B" . date("ymd") . str_pad((self::whereDate('created_at', '=', date('Y-m-d'))
                        ->withTrashed()
                        ->get()
                        ->count()) + 1, 3, '0', STR_PAD_LEFT);

            $id = self::create([
                "purchase_id" => $purchase_id,
                "usr_users_id" => $usr_users_id,
                "type" => $type,
                "sn" => $sn,
                "price" => $price,
                "pay_date" => $pay_date,
                "memo" => $memo
            ])->id;

            return $id;
        });
    }

    public static function getPayingOrdersWithPurchaseID($purchase_id)
    {
        $result = DB::table('pcs_paying_orders as paying_order')
            ->select('paying_order.id as id'
                , 'paying_order.type as type'
                , 'paying_order.sn as sn'
                , 'paying_order.price as price'
            )
            ->selectRaw('DATE_FORMAT(paying_order.pay_date,"%Y-%m-%d") as pay_date')

            ->where('paying_order.purchase_id', '=', $purchase_id)
            ->whereNull('paying_order.deleted_at');
        return $result;
    }
}
