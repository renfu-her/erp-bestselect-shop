<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RptUserReportMonthly extends Model
{
    use HasFactory;
    protected $table = 'rpt_user_report_monthly';
    protected $guarded = [];
    public $timestamps = false;

    public static function dataList($type = null, $year = null, $options = [])
    {

        $sub = DB::table('rpt_user_report_monthly')
            ->select(['user_id'])
            ->selectRaw('SUM(on_price) as on_price')
            ->selectRaw('SUM(on_gross_profit) as on_gross_profit')
            ->selectRaw('SUM(off_price) as off_price')
            ->selectRaw('SUM(off_gross_profit) as off_gross_profit')
            ->selectRaw('SUM(total_price) as total_price')
            ->selectRaw('SUM(total_gross_profit) as total_gross_profit');

        // 時間區間
        $_date = RptReport::dateRange($type, $year, $options);

        $sub->whereBetween('month', [$_date[0], $_date[1]])
            ->groupBy('user_id');

        $re = DB::table('usr_user_organize as organize')
            ->leftJoin('usr_users as user', 'organize.title', '=', 'user.group')
            ->leftJoinSub($sub, 'report', 'user.id', '=', 'report.user_id')
            ->select(['user.id', 'user.name as title'])
            ->selectRaw('IFNULL(report.on_price, 0) as on_price')
            ->selectRaw('IFNULL(report.on_gross_profit, 0) as on_gross_profit')
            ->selectRaw('IFNULL(report.off_price, 0) as off_price')
            ->selectRaw('IFNULL(report.off_gross_profit, 0) as off_gross_profit')
            ->selectRaw('IFNULL(report.total_gross_profit, 0) as total_gross_profit')
            ->selectRaw('IFNULL(report.total_price, 0) as total_price');

        if (isset($options['group']) && $options['group']) {
            $re->where('organize.id', $options['group']);
        }

        return $re;
    }

    public static function userOrder($type = null, $year = null, $options = [])
    {
        $_date = RptReport::dateRange($type, $year, $options);
       
        $re = DB::table('ord_orders as order')
            ->leftJoin('ord_received_orders as ro', 'order.id', '=', 'ro.source_id')
            ->select(['order.sn', 'order.id', 'order.origin_price', 'order.gross_profit'])
            ->whereBetween('ro.receipt_date', $_date);

        if (isset($options['user_id'])) {
            $re->leftJoin('usr_customers as customer', 'order.mcode', '=', 'customer.sn')
                ->leftJoin('usr_users as user', 'user.customer_id', '=', 'customer.id')
                ->where('user.id', $options['user_id']);
        }

        return $re;

    }
    // 毛利計算
    public static function grossProfit()
    {
        $order = Order::select('id')->where('gross_profit', 0)->get()->toArray();

        $atomic = RptReport::atomic()->whereIn('order.id', array_map(function ($n) {
            return $n['id'];
        }, $order));

        $re = DB::table(DB::raw("({$atomic->toSql()}) as atomic"))
            ->mergeBindings($atomic)
            ->select('atomic.order_id')
            ->selectRaw('SUM(atomic.total_price) as total_price')
            ->selectRaw('SUM(atomic.gross_profit) as gross_profit')
            ->groupBy('atomic.order_id')
            ->get();

        foreach ($re as $value) {
            if ($value->gross_profit > 0) {
                Order::where('id', $value->order_id)->update([
                    'gross_profit' => $value->gross_profit,
                ]);
            }
        }

    }

    public static function report($date = null)
    {

        if (!$date) {
            $d = strtotime(date('Y-m-d') . " -1 day");
            $sdate = Date("Y-m-1 00:00:00", $d);
            $edate = Date("Y-m-t 23:59:59", $d);
        } else {
            $sdate = Date("Y-m-1 00:00:00", strtotime($date));
            $edate = Date("Y-m-t 23:59:59", strtotime($date));
        }

        $currentMonth = Date("Y-m-01", strtotime($sdate));

        $atomic = RptReport::atomic();

        $re = DB::table(DB::raw("({$atomic->toSql()}) as atomic"))
            ->mergeBindings($atomic)
            ->leftJoin('prd_sale_channels as sh', 'atomic.sale_channel_id', '=', 'sh.id')
            ->leftJoin('usr_customers as customer', 'customer.sn', '=', 'atomic.mcode')
            ->join('usr_users as user', 'user.customer_id', '=', 'customer.id')
            ->select(['customer.email', 'user.id as user_id', 'sh.sales_type'])
            ->selectRaw('SUM(atomic.total_price) as total_price')
            ->selectRaw('SUM(atomic.gross_profit) as gross_profit')
            ->selectRaw('DATE_FORMAT(atomic.receipt_date, "%Y-%m-01") as dd')
            ->whereBetween('atomic.receipt_date', [$sdate, $edate])
            ->groupBy('dd')
            ->groupBy('atomic.email')
            ->groupBy('sh.sales_type')->get();

        $user = [];
        foreach ($re as $value) {
            if (!isset($user[$value->user_id])) {
                $user[$value->user_id] = ['0' => [], '1' => []];
            }

            $user[$value->user_id][$value->sales_type] = $value;
        }

        self::where('month', $currentMonth)->delete();

        self::insert(array_map(function ($n, $idx) use ($currentMonth) {
            $data = ['user_id' => $idx,
                'total_price' => 0,
                'total_gross_profit' => 0,
                'month' => $currentMonth,
                'on_price' => 0,
                'on_gross_profit' => 0,
                'off_price' => 0,
                'off_gross_profit' => 0,
            ];

            if ($n[0]) {
                $data['on_price'] = $n[0]->total_price;
                $data['on_gross_profit'] = $n[0]->gross_profit;
                $data['total_price'] += $n[0]->total_price;
                $data['total_gross_profit'] += $n[0]->gross_profit;
            }

            if ($n[1]) {
                $data['off_price'] = $n[1]->total_price;
                $data['off_gross_profit'] = $n[1]->gross_profit;
                $data['total_price'] += $n[1]->total_price;
                $data['total_gross_profit'] += $n[1]->gross_profit;
            }

            return $data;

        }, $user, array_keys($user)));

    }
}
