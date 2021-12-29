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

    //建立採購單
    public static function createPurchase($purchase_id, $product_style_id, $title, $sku, $price, $num, $temp_id, $memo = null)
    {
        return DB::transaction(function () use (
            $purchase_id,
            $product_style_id,
            $title,
            $sku,
            $price,
            $num,
            $temp_id,
            $memo
        ) {
            $id = self::create([
                "purchase_id" => $purchase_id,
                "product_style_id" => $product_style_id,
                "title" => $title,
                "sku" => $sku,
                "price" => $price,
                "num" => $num,
                "temp_id" => $temp_id,
                "memo" => $memo
            ])->id;

            return $id;
        });
    }

    //採購單入庫 更新資料
    public static function updatePurchase($id, $expiry_date = null, $temp_id, $status = 0, $inbound_date = null, $inbound_num = 0, $depot_id = null, $inbound_id = null, $error_num = 0, $memo = null)
    {
        return DB::transaction(function () use (
            $id,
            $expiry_date,
            $temp_id,

            $status,
            $inbound_date,
            $inbound_num,
            $depot_id,
            $inbound_id,
            $error_num,
            $memo
        ) {
            self::where('id', $id)->update([
                "expiry_date" => $expiry_date,
                "temp_id" => $temp_id,

                "status" => $status,
                "inbound_date" => $inbound_date,
                "inbound_num" => $inbound_num,
                "depot_id" => $depot_id,
                "inbound_id" => $inbound_id,
                "error_num" => $error_num,
                "memo" => $memo
            ]);

            return $id;
        });
    }

    public static function getData($purchase_id) {
        return self::where('purchase_id', $purchase_id)->whereNull('deleted_at');
    }

}
