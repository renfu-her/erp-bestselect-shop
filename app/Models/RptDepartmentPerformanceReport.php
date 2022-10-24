<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RptDepartmentPerformanceReport extends Model
{
    use HasFactory;

    protected $table = 'rpt_department_performance_daily_report';
    protected $guarded = [];
    public $timestamps = false;

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

        foreach (['department', 'group'] as $value) {

            $datas = DB::table('ord_orders as order')
                ->leftJoin('ord_received_orders as ro', 'order.id', '=', 'ro.source_id')
                ->join('usr_customers as customer', 'order.mcode', '=', 'customer.sn')
                ->leftJoin('usr_users as user', 'user.customer_id', '=', 'customer.id')
                ->leftJoin('usr_user_organize as organize', 'organize.title', '=', 'user.' . $value)
                ->select('organize.id as organize_id')
                ->selectRaw('DATE_FORMAT(ro.receipt_date, "%Y-%m-%d") as dd')
                ->selectRaw('SUM(order.total_price) as total_price')
                ->whereBetween('ro.receipt_date', [$sdate, $edate])
                ->where('organize.id', "<>", 0)
                ->groupBy('dd')
                ->groupBy('organize.id');

            self::insert(array_map(function ($n) {
                return ['organize_id' => $n->organize_id,
                    'date' => $n->dd,
                    'price' => $n->total_price,
                ];
            }, $datas->get()->toArray()));
        }

    }

}
