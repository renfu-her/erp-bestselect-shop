<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderMonthProfitReport extends Model
{
    use HasFactory;
    protected $table = 'ord_month_profit_report';
    protected $guarded = [];

    public static function dataList($keyword = null, $month = null)
    {

        $re = DB::table('ord_month_profit_report as report')
            ->select('report.*')
            ->selectRaw('DATE_FORMAT(report.report_at, "%Y/%m") as report_at');

        if ($keyword) {
            $re->where('report.title', 'like', "%$keyword%");
        }

        if ($month) {
            $sdate = date("Y-m-1", strtotime($month));
            $edate = date("Y-m-t", strtotime($month));
            $re->whereBetween('report.report_at', [$sdate, $edate]);
        }

        return $re;
    }

    public static function createReport($title, $date, $user_id, $transfer_at)
    {
        DB::beginTransaction();

        $sdate = date("Y-m-1", strtotime($date));
        $edate = date("Y-m-t", strtotime($date));

        $profits = DB::table('ord_order_profit as profit')
            ->join('ord_sub_orders as sub_order', 'profit.sub_order_id', '=', 'sub_order.id')
            ->select(DB::raw("DATE_FORMAT(sub_order.dlv_audit_date, '%Y/%m') as dlv_audit_date"))
            ->selectRaw('SUM(profit.bonus) as bonus')
            ->selectRaw('count(*) as qty')
            ->whereBetween('sub_order.dlv_audit_date', [$sdate, $edate])
            ->groupBy(DB::raw("DATE_FORMAT(sub_order.dlv_audit_date, '%Y/%m')"))->get()->first();

        if (!$profits) {
            return ['success' => '0', 'message' => '無資料'];
        }

        $id = self::create([
            'title' => $title,
            'bonus' => $profits->bonus,
            'qty' => $profits->qty,
            'report_at' => $profits->dlv_audit_date . "/1",
            'transfer_at' => $transfer_at,
            'create_user_id' => $user_id,
        ])->id;

        OrderCustomerProfitReport::createCustomerReport($id, $date);

        DB::commit();

        return ['success' => '1'];

    }

    public static function deleteReport($id)
    {
        DB::beginTransaction();

        self::where('id', $id)->delete();

        OrderCustomerProfitReport::where('month_profit_report_id', $id)->delete();

        DB::commit();
    }
}
