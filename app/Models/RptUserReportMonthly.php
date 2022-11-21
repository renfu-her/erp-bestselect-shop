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

    public static function dataList($sdate, $edate, $options = [])
    {
        // 改成daily
        $sub = DB::table('rpt_user_report_monthly')
            ->select(['user_id'])
            ->selectRaw('SUM(on_price) as on_price')
            ->selectRaw('SUM(on_gross_profit) as on_gross_profit')
            ->selectRaw('SUM(off_price) as off_price')
            ->selectRaw('SUM(off_gross_profit) as off_gross_profit')
            ->selectRaw('SUM(total_price) as total_price')
            ->selectRaw('SUM(total_gross_profit) as total_gross_profit');

        // 時間區間
        //  $_date = RptReport::dateRange($type, $year, $options);

        $sub->whereBetween('month', [$sdate, $edate])
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
            ->selectRaw('IFNULL(report.total_price, 0) as total_price')
            ->whereNotNull('user.id');

        if (isset($options['group']) && $options['group']) {
            $re->where('organize.id', $options['group']);
        }

        return $re;
    }

    public static function userOrder($sdate = null, $edate = null, $options = [])
    {

        $re = DB::table('ord_orders as order')
            ->leftJoin('ord_received_orders as ro', 'order.id', '=', 'ro.source_id')
            ->leftJoin('prd_sale_channels as sale_channel', 'sale_channel.id', '=', 'order.sale_channel_id')
            ->select(['order.sn', 'order.id', 'order.origin_price', 'order.gross_profit', 'sale_channel.sales_type'])
            ->whereBetween('ro.receipt_date', [$sdate, $edate])
            ->where('order.status_code', 'received');

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
        $order = DB::table('ord_orders as order')
            ->leftJoin('ord_received_orders as ro', 'order.id', '=', 'ro.source_id')
            ->select('order.id')
            ->whereNotNull('ro.receipt_date')
            ->where(function ($query) {
                $query->whereNull('order.gross_profit')
                    ->orWhere('order.gross_profit', 0);
            })

            ->get()
            ->toArray();

        $atomic = RptReport::atomic()->whereIn('order.id', array_map(function ($n) {
            return $n->id;
        }, $order));

        $re = DB::table(DB::raw("({$atomic->toSql()}) as atomic"))
            ->mergeBindings($atomic)
            ->select('atomic.order_id')
            ->selectRaw('SUM(atomic.total_price) as total_price')
            ->selectRaw('SUM(atomic.gross_profit) as gross_profit')
            ->groupBy('atomic.order_id')
            ->get();

        foreach ($re as $value) {
            //  if ($value->gross_profit > 0) {
            Order::where('id', $value->order_id)->update([
                'gross_profit' => $value->gross_profit,
            ]);
            //  }
        }

    }

    public static function report($date = null, $type = "date")
    {
        // 算商品毛利淨利
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

        self::where('month', 'like', "%$currentMonth%")->delete();

        $atomic = RptReport::atomic();

        $re = DB::table(DB::raw("({$atomic->toSql()}) as atomic"))
            ->mergeBindings($atomic)
            ->leftJoin('prd_sale_channels as sh', 'atomic.sale_channel_id', '=', 'sh.id')
            ->leftJoin('usr_customers as customer', 'customer.sn', '=', 'atomic.mcode')
            ->join('usr_users as user', 'user.customer_id', '=', 'customer.id')
            ->select(['customer.email', 'user.id as user_id', 'sh.sales_type'])
            ->selectRaw('SUM(atomic.total_price) as total_price')
            ->selectRaw('SUM(atomic.gross_profit) as gross_profit')
            ->selectRaw('DATE_FORMAT(atomic.receipt_date, "%Y-%m-%d") as dd')
            ->whereBetween('atomic.receipt_date', [$sdate, $edate])
            ->groupBy('dd')
            ->groupBy('atomic.email')
            ->groupBy('sh.sales_type')->get();

        $user = [];
        foreach ($re as $value) {
            if (!isset($user[$value->user_id])) {
                //  $user[$value->user_id] = ['0' => [], '1' => []];
                $user[$value->user_id] = [];
            }
            if (!isset($user[$value->user_id][$value->dd])) {
                $user[$value->user_id][$value->dd] = ['0' => [], '1' => []];
            }

            $user[$value->user_id][$value->dd][$value->sales_type][] = $value;
        }
        $insertData = [];
        foreach ($user as $uid => $u) {
            foreach ($u as $dd => $data) {
                $d = ['user_id' => $uid,
                    'total_price' => 0,
                    'total_gross_profit' => 0,
                    'on_price' => 0,
                    'on_gross_profit' => 0,
                    'off_price' => 0,
                    'off_gross_profit' => 0,
                    'month' => $dd,
                ];

                if (isset($data[0]) && count($data[0]) > 0) {
                    foreach ($data[0] as $d1) {
                        self::subCacu($d, $d1, 'off');
                    }
                }

                if (isset($data[1]) && count($data[1]) > 0) {
                    foreach ($data[1] as $d1) {
                        self::subCacu($d, $d1, 'on');
                    }
                }

                $insertData[] = $d;
            }
        }

        self::insert($insertData);

    }

    private static function subCacu(&$data, $d, $prefix)
    {
        $data[$prefix . '_price'] += $d->total_price;
        $data[$prefix . '_gross_profit'] += $d->gross_profit;
        $data['total_price'] += $d->total_price;
        $data['total_gross_profit'] += $d->gross_profit;
    }
}
