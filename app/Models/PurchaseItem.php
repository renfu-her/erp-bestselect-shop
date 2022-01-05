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
    public static function createPurchase($purchase_id, $product_style_id, $title, $sku, $price, $num, $temp_id = null, $memo = null)
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


    public static function getData($purchase_id) {
        return self::where('purchase_id', $purchase_id)->whereNull('deleted_at');
    }

}
