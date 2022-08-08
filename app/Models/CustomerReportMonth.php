<?php

namespace App\Models;

use App\Enums\Order\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CustomerReportMonth extends Model
{
    use HasFactory;

    protected $table = 'usr_customer_report_month';
    protected $guarded = [];

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
            ->select('order.mcode')
            ->selectRaw('SUM(order.total_price) as price')
            ->where('order.payment_status', PaymentStatus::Received())
            ->whereBetween('ro.receipt_date', [$sdate, $edate])
            ->whereNotNull('order.mcode')
            ->groupBy('order.mcode')->get();

       
        foreach ($re as $data) {
            $customer = Customer::where('sn', $data->mcode)->get()->first();

            if ($customer) {
                if (self::where('date', Date("Y-m-1"))->where('customer_id', $customer->id)->get()->first()) {
                    self::where('date', Date("Y-m-1"))->where('customer_id', $customer->id)->update([
                        'price' => $data->price ? $data->price : 0,

                    ]);
                } else {
                    self::create([
                        'date' => Date("Y-m-1"),
                        'price' => $data->price ? $data->price : 0,
                        'customer_id' => $customer->id,
                    ]);
                }
            }
        }

        $re->sdate = $sdate;
        $re->edate = $edate;

        return $re;

    }

    public static function dataList()
    {
        return DB::table('usr_customer_report_month as month')
            ->leftJoin('usr_customers as customer', 'month.customer_id', '=', 'customer.id')
            ->select('customer.name', 'month.price')
            ->orderBy('month.price', 'DESC');
    }
}
