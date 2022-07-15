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

    public static function dataList($keyword = null, $month = null, $check_status = null)
    {

        $re = DB::table('ord_customer_profit_report as report')
            ->select('report.*', 'customer.name', 'customer.sn as mcode')
            ->selectRaw('DATE_FORMAT(report.report_at, "%Y/%m") as report_at')
            ->leftJoin('usr_customers as customer', 'report.customer_id', '=', 'customer.id');

        if ($keyword) {
            $re->where(function ($query) use ($keyword) {
                $query->where('customer.name', 'like', "%$keyword%")
                    ->orWhere('customer.sn', 'like', "%$keyword%");
            });
        }

        if ($month) {
            $sdate = date("Y-m-1", strtotime($month));
            $edate = date("Y-m-t", strtotime($month));
            $re->whereBetween('report.report_at', [$sdate, $edate]);
        }

        if (isset($check_status) && $check_status != 'all') {
            if ($check_status == '1') {
                $re->whereNotNull('report.checked_at');
            } else {
                $re->whereNull('report.checked_at');
            }
        }

        return $re;
    }

    public static function createMonthReport($date)
    {

        DB::beginTransaction();
        $sdate = date("Y-m-1", strtotime($date));
        $edate = date("Y-m-t", strtotime($date));
        //  dd($date);
        $profits = DB::table('ord_order_profit as profit')
            ->join('ord_sub_orders as sub_order', 'profit.sub_order_id', '=', 'sub_order.id')
            ->join('ord_orders as order', 'profit.order_id', '=', 'order.id')
            ->select('profit.customer_id')
            ->selectRaw('SUM(profit.bonus) as bonus')
            ->selectRaw('count(*) as qty')
            ->whereBetween('sub_order.dlv_audit_date', [$sdate, $edate])
            ->groupBy('profit.customer_id')->get();

        foreach ($profits as $profit) {

            self::create([
                'customer_id' => $profit->customer_id,
                'bonus' => $profit->bonus,
                'qty' => $profit->qty,
                'report_at' => $sdate,
            ]);
        }

        DB::commit();
    }
}
