<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PurchaseItem extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'pcs_purchase_items';
    protected $guarded = [];

    public static function createPurchase($ps_id, $title, $price, $num, $expiry_date = null, $temp_id, $status = 0, $inbound_status = 0, $inbound_date = null, $inbound_num = 0, $depot_id = null, $inbound_id = null, $sale_num = 0, $error_num = 0, $memo = null)
    {
        return DB::transaction(function () use (
            $ps_id,
            $title,
            $price,
            $num,
            $expiry_date,
            $temp_id,
            $status,
            $inbound_status,
            $inbound_date,
            $inbound_num,
            $depot_id,
            $inbound_id,
            $sale_num,
            $error_num,
            $memo
        ) {
            $id = self::create([
                "ps_id" => $ps_id,
                "title" => $title,
                "price" => $price,
                "num" => $num,
                "expiry_date" => $expiry_date,
                "temp_id" => $temp_id,
                "status" => $status,
                "inbound_status" => $inbound_status,
                "inbound_date" => $inbound_date,
                "inbound_num" => $inbound_num,
                "depot_id" => $depot_id,
                "inbound_id" => $inbound_id,
                "sale_num" => $sale_num,
                "error_num" => $error_num,
                "memo" => $memo
            ])->id;

            return $id;
        });
    }
}
