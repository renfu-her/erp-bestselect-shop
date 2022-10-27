<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RptUserPerformanceReport extends Model
{
    use HasFactory;
    protected $table = 'rpt_user_performance_daily_report';
    protected $guarded = [];
    public $timestamps = false;

    // 算訂單總金額
    public static function report($date = null, $type = "date")
    {

        $date = $date ? $date : date("Y-m-d 00:00:00", strtotime(now() . " -1 days"));

        switch ($type) {
            case 'date':
                $sdate = date("Y-m-d 00:00:00", strtotime($date));
                $edate = date("Y-m-d 23:59:59", strtotime($date));
                $currentMonth = Date("Y-m-d", strtotime($sdate));
                break;
            case 'month':
                $sdate = date("Y-m-01 00:00:00", strtotime($date));
                $edate = date("Y-m-t 23:59:59", strtotime($date));
                $currentMonth = Date("Y-m", strtotime($sdate));
                break;
        }

        self::where('date', 'like', "%$currentMonth%")->delete();

        $datas = DB::table('ord_orders as order')
            ->leftJoin('ord_received_orders as ro', 'order.id', '=', 'ro.source_id')
            ->join('usr_customers as customer', 'order.mcode', '=', 'customer.sn')
            ->leftJoin('usr_users as user', 'user.customer_id', '=', 'customer.id')
            ->select('user.id as user_id')
            ->selectRaw('DATE_FORMAT(ro.receipt_date, "%Y-%m-%d") as dd')
            ->selectRaw('SUM(order.total_price) as total_price')
            ->whereBetween('ro.receipt_date', [$sdate, $edate])
            ->where('order.status_code','received')
            ->groupBy('dd')
            ->groupBy('user.id');

        self::insert(array_map(function ($n) {
            return ['user_id' => $n->user_id,
                'date' => $n->dd,
                'price' => $n->total_price,
            ];
        }, $datas->get()->toArray()));

    }

    public static function dataList($sdate, $edate, $options = [])
    {
        $sub = DB::table('rpt_user_performance_daily_report')
            ->select(['user_id'])
            ->selectRaw('SUM(price) as price')
            ->whereBetween('date', [$sdate, $edate])
            ->groupBy('user_id');

        $re = DB::table('usr_users as user')
            ->select(['user.name', 'report.price', 'user.department', 'user.group'])
            ->selectRaw('IF(report.price IS NULL,0,report.price) as price')
            ->leftJoinSub($sub, 'report', 'user.id', '=', 'report.user_id')
            ->orderBy('report.price', 'DESC')
            ->orderBy('user.department', 'DESC');

        if (isset($options['department'])) {
            if (is_array($options['department']) && count($options['department']) > 0) {
                $re->whereIn('user.department', $options['department']);
            } else if ($options['department']) {
                $re->where('user.department', $options['department']);
            }
        }

        return $re;

    }

}
