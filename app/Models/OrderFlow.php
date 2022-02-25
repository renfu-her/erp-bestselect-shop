<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderFlow extends Model
{
    use HasFactory;
    protected $table = 'ord_order_flow';
    protected $guarded = [];

    public static function changeOrderStatus($order_id, $code)
    {
        $code = DB::table('ord_order_status')->where('code', $code)->get()->first();

        Order::where('id', $order_id)->update([
            'status' => $code->title,
            'status_code' => $code->code,
        ]);

        self::create([
            'order_id' => $order_id,
            'status' => $code->title,
            'status_code' => $code->code,
        ]);
    }
}
