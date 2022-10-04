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

    public static function report($date = '2022-09-01')
    {

        if (!$date) {
            $sdate = Date("Y-m-1 00:00:00");
            $edate = Date("Y-m-t 23:59:59");
        } else {
            $sdate = Date("Y-m-1 00:00:00", strtotime($date));
            $edate = Date("Y-m-t 23:59:59", strtotime($date));
        }

        $currentMonth = Date("Y-m-01", strtotime($sdate));

        $atomic = RptReport::atomic();

        $re = DB::table(DB::raw("({$atomic->toSql()}) as atomic"))
            ->mergeBindings($atomic)
            ->leftJoin('prd_sale_channels as sh', 'atomic.sale_channel_id', '=', 'sh.id')
            ->leftJoin('usr_customers as customer', 'customer.email', '=', 'atomic.email')
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
