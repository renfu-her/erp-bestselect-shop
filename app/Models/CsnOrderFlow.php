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

    public static function changeOrderStatus($order_id, OrderStatus $status)
    {
        CsnOrder::where('id', $order_id)->update([
            'status_code' => $status->value,
            'status' => $status->description,
        ]);

        self::create([
            'order_id' => $order_id,
            'status_code' => $status->value,
            'status' => $status->description,
        ]);
    }
}
