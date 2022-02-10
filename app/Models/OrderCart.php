<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCart extends Model
{
    use HasFactory;
    protected $table = 'ord_cart';
    protected $guarded = [];
    public $timestamps = false;

    public static function productAdd($customer_id, $product_id, $product_style_id, $qty, $shipment_type, $shipment_event_id = null)
    {
        if (!self::where('customer_id', $customer_id)->where('product_id', $product_id)->where('product_style_id', $product_style_id)->get()->first()) {
            self::create([
                'customer_id' => $customer_id,
                'product_id' => $product_id,
                'product_style_id' => $product_style_id,
                'qty' => $qty,
                'shipment_type' => $shipment_type,
                'shipment_event_id' => $shipment_event_id,
            ]);
        }
    }

    public static function productRemove($id)
    {
        self::where('id', $id)->delete();
    }

    public static function productUpdate($id, $qty)
    {
        self::where('id', $id)->update(['qty' => $qty]);
    }

}
