<?php

namespace App\Models;

use App\Enums\Order\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderReportMonth extends Model
{
    use HasFactory;
    protected $table = 'ord_order_report_month';
    protected $guarded = [];
    public $timestamps = true;

    public static function createData($date = null)
    {
        if (!$date) {
            $sdate = Date("Y-m-1 00:00:00");
            $edate = Date("Y-m-t 23:59:59");
        } else {
            $sdate = Date("Y-m-1 00:00:00", strtotime($date));
            $edate = Date("Y-m-t 23:59:59", strtotime($date));
        }

        $re = DB::table('ord_orders as order')
            ->leftJoin('ord_received_orders as ro', 'order.id', '=', 'ro.source_id')
            ->selectRaw('SUM(order.total_price) as price')
            ->selectRaw('COUNT(*) as qty')
            ->where('order.payment_status', PaymentStatus::Received())
            ->whereBetween('ro.receipt_date', [$sdate, $edate])
            ->groupBy()->get()->first();

        if (self::where('date', Date("Y-m-1", strtotime($sdate)))->get()->first()) {
            self::where('date', Date("Y-m-1", strtotime($sdate)))->update([
                'price' => $re->price ? $re->price : 0,
                'qty' => $re->qty,
            ]);
        } else {
            self::create([
                'date' => Date("Y-m-1", strtotime($sdate)),
                'price' => $re->price ? $re->price : 0,
                'qty' => $re->qty,
            ]);
        }

    }
}
