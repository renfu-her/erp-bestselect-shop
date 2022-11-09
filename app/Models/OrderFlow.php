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

        //若訂單取消則不改變狀態 只改變flow表
        //否則在退貨時修改狀態 後面判斷訂單取消將導致可售數量錯亂
        $order = Order::where('id', $order_id)->first();
        if (OrderStatus::Canceled()->value != $order->status_code) {
            Order::where('id', $order_id)->update([
                'status_code' => $status->value,
                'status' => $status->description,
            ]);
        }

        self::create([
            'order_id' => $order_id,
            'status_code' => $status->value,
            'status' => $status->description,
            'create_user_id' => Auth::user()->id ?? null,
            'create_user_name' => Auth::user()->name ?? null,
        ]);
    }
}
