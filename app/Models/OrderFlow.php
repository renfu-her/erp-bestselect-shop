<?php

namespace App\Models;

use App\Enums\Order\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class OrderFlow extends Model
{
    use HasFactory;
    protected $table = 'ord_order_flow';
    protected $guarded = [];

    public static function changeOrderStatus($order_id, OrderStatus $status)
    {

        Order::where('id', $order_id)->update([
            'status_code' => $status->value,
            'status' => $status->description,
        ]);

        self::create([
            'order_id' => $order_id,
            'status_code' => $status->value,
            'status' => $status->description,
            'create_user_id' => Auth::user()->id ?? null,
            'create_user_name' => Auth::user()->name ?? null,
        ]);
    }
}
