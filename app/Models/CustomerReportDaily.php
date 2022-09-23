<?php

namespace App\Models;

use App\Enums\Order\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CustomerReportDaily extends Model
{
    use HasFactory;

    protected $table = 'usr_customer_report_daily';
    protected $guarded = [];

    public static function createData($date = null)
    {
        $sdate = Date("Y-m-d 00:00:00");
        $edate = Date("Y-m-d 23:59:59");

        $re = DB::table('ord_orders as order')
            ->leftJoin('ord_received_orders as ro', 'order.id', '=', 'ro.source_id')
            ->select('order.mcode')
            ->selectRaw('SUM(order.total_price) as price')
            ->where('order.payment_status', PaymentStatus::Received())
            ->whereBetween('ro.receipt_date', [$sdate, $edate])
            ->whereNotNull('order.mcode')
            ->groupBy('order.mcode')->get();

        $currentDate = $date ? Date("Y-m-d", strtotime($date)) : Date("Y-m-d");
      
        self::where('date', $currentDate)->delete();

        foreach ($re as $data) {
            $customer = Customer::where('sn', $data->mcode)->get()->first();

            if ($customer) {
                self::create([
                    'date' => $currentDate,
                    'price' => $data->price ? $data->price : 0,
                    'customer_id' => $customer->id,
                ]);
                /*
            if (self::where('date', Date("Y-m-d"))->where('customer_id', $customer->id)->get()->first()) {
            self::where('date', Date("Y-m-d"))->where('customer_id', $customer->id)->update([
            'price' => $data->price ? $data->price : 0,

            ]);
            } else {
            self::create([
            'date' => Date("Y-m-d"),
            'price' => $data->price ? $data->price : 0,
            'customer_id' => $customer->id,
            ]);
            }
             */
            }
        }

    }

    public static function dataList()
    {

        return DB::table('usr_customer_report_daily as daily')
            ->leftJoin('usr_customers as customer', 'daily.customer_id', '=', 'customer.id')
            ->select('customer.name', 'daily.price')
            ->orderBy('daily.price', 'DESC');

    }

}
