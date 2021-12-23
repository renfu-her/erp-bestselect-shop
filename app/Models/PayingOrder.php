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

    public static function createPayingOrder($purchase_id, $type, $order_num, $price = null, $pay_date = null, $memo = null)
    {
        return DB::transaction(function () use (
            $purchase_id,
            $type,
            $order_num,
            $price,
            $pay_date,
            $memo
        ) {
            $id = self::create([
                "purchase_id" => $purchase_id,
                "type" => $type,
                "order_num" => $order_num,
                "price" => $price,
                "pay_date" => $pay_date,
                "memo" => $memo
            ])->id;

            return $id;
        });
    }
}
