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
        $bank_cname = null,
        $bank_code = null,
        $bank_acount = null,
        $bank_numer = null,
        $price = null,
        $pay_date = null,
        $logistic_price = 0,
        $memo = null
    ) {
        return DB::transaction(function () use (
            $purchase_id,
            $usr_users_id,
            $type,
            $bank_cname,
            $bank_code,
            $bank_acount,
            $bank_numer,
            $price,
            $pay_date,
            $logistic_price,
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
                'bank_cname' => $bank_cname,
                'bank_code' => $bank_code,
                'bank_acount' => $bank_acount,
                'bank_numer' => $bank_numer,
                "price" => $price,
                "pay_date" => $pay_date,
                'logistic_price' => $logistic_price,
                "memo" => $memo
            ])->id;

            return $id;
        });
    }
}
