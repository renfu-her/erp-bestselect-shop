<?php

namespace App\Models;

use App\Enums\Order\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsnOrderFlow extends Model
{
    use HasFactory;
    protected $table = 'csn_order_flow';
    protected $guarded = [];

    public static function changeOrderStatus($order_id, OrderStatus $stauts)
    {
        CsnOrder::where('id', $order_id)->update([
            'status_code' => $stauts->value,
            'status' => $stauts->description,
        ]);

        self::create([
            'order_id' => $order_id,
            'status_code' => $stauts->value,
            'status' => $stauts->description,
        ]);
    }
}
