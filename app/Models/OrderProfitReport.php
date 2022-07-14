<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderProfitReport extends Model
{
    use HasFactory;
    protected $table = 'ord_customer_profit_report';
    protected $guarded = [];

    public static function dataList()
    {

        $re = DB::table('ord_customer_profit_report as report')
            ->select('report.*', 'customer.name')
            ->selectRaw('DATE_FORMAT(report.report_at, "%Y/%m") as report_at')
            ->leftJoin('usr_customers as customer', 'report.customer_id', '=', 'customer.id');

        return $re;
    }

    public static function createMonthReport($date)
    {

        DB::beginTransaction();
        $date = date("Y-m-1", strtotime('2022/6/10'));

        $profits = DB::table('ord_order_profit as profit')
            ->join('ord_orders as order', 'profit.order_id', '=', 'order.id')
            ->select('profit.customer_id')
            ->selectRaw('SUM(profit.bonus) as bonus')
            ->selectRaw('count(*) as qty')
            ->groupBy('profit.customer_id')->get();

        foreach ($profits as $profit) {

            self::create([
                'customer_id' => $profit->customer_id,
                'bonus' => $profit->bonus,
                'qty' => $profit->qty,
                'report_at' => $date,
            ]);
        }

        DB::commit();
    }
}
