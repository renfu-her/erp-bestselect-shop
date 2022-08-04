<?php

namespace App\Models;

use App\Enums\Order\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderReportDaily extends Model
{
    use HasFactory;
    protected $table = 'ord_order_report_daily';
    protected $guarded = [];
    public $timestamps = true;

    public static function createData()
    {
        $sdate = Date("Y-m-d 00:00:00");
        $edate = Date("Y-m-d 23:59:59");

        $re = DB::table('ord_orders as order')
            ->leftJoin('ord_received_orders as ro', 'order.id', '=', 'ro.source_id')
            ->selectRaw('SUM(order.total_price) as price')
            ->selectRaw('COUNT(*) as qty')
            ->where('order.payment_status', PaymentStatus::Received())
            ->whereBetween('ro.receipt_date', [$sdate, $edate])
            ->groupBy()->get()->first();

        if (self::where('date', Date("Y-m-d"))->get()->first()) {
            self::where('date', Date("Y-m-d"))->update([
                'price' => $re->price ? $re->price : 0,
                'qty' => $re->qty,
            ]);
        } else {
            self::create([
                'date' => Date("Y-m-d"),
                'price' => $re->price ? $re->price : 0,
                'qty' => $re->qty,
            ]);
        }

    }
}
