<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RptOrganizeReportMonthly extends Model
{
    use HasFactory;
    protected $table = 'rpt_organize_report_monthly';
    protected $guarded = [];
    public $timestamps = false;

    public static function dataList($sdate, $edate, $options = [])
    {
        $sub = DB::table('rpt_organize_report_monthly')
            ->select(['organize_id'])
            ->selectRaw('SUM(on_price) as on_price')
            ->selectRaw('SUM(on_gross_profit) as on_gross_profit')
            ->selectRaw('SUM(off_price) as off_price')
            ->selectRaw('SUM(off_gross_profit) as off_gross_profit')
            ->selectRaw('SUM(total_price) as total_price')
            ->selectRaw('SUM(total_gross_profit) as total_gross_profit')
            ->selectRaw('SUM(users) as users')
            ->whereBetween('month', [$sdate, $edate])
            ->groupBy('organize_id');

        $re = DB::table('usr_user_organize as organize')
            ->leftJoinSub($sub, 'report', 'organize.id', '=', 'report.organize_id')
            ->select(['organize.id', 'organize.title'])
            ->selectRaw('IFNULL(report.on_price, 0) as on_price')
            ->selectRaw('IFNULL(report.on_gross_profit, 0) as on_gross_profit')
            ->selectRaw('IFNULL(report.off_price, 0) as off_price')
            ->selectRaw('IFNULL(report.off_gross_profit, 0) as off_gross_profit')
            ->selectRaw('IFNULL(report.total_gross_profit, 0) as total_gross_profit')
            ->selectRaw('IFNULL(report.total_price, 0) as total_price')
            ->selectRaw('IFNULL(report.users, 0) as users');

        if (isset($options['level'])) {
            $re->where('level', $options['level']);
        }

        if (isset($options['department']) && $options['department']) {
            if (is_array($options['department'])) {
                $re->whereIn('organize.id', $options['department']);
            } else {
                $re->where('organize.id', $options['department']);
            }
        }

        if (isset($options['parent']) && $options['parent']) {
            $re->where('organize.parent', $options['parent']);
        }

        return $re;
    }

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

        self::where('month','like', "%$currentMonth%")->delete();
        // 三階
        $re = DB::table('usr_user_organize as org')
            ->leftJoin('usr_users as user', 'org.title', '=', 'user.group')
            ->leftJoin('rpt_user_report_monthly as report', 'user.id', '=', 'report.user_id')
            ->select(['org.id as organize_id', 'report.month'])
            ->selectRaw('SUM(on_price) as on_price')
            ->selectRaw('SUM(on_gross_profit) as on_gross_profit')
            ->selectRaw('SUM(off_price) as off_price')
            ->selectRaw('SUM(off_gross_profit) as off_gross_profit')
            ->selectRaw('SUM(total_price) as total_price')
            ->selectRaw('SUM(total_gross_profit) as total_gross_profit')
            ->selectRaw('COUNT(user.id) as users')
            ->whereBetween('report.month', [$sdate, $edate])
            ->where('org.level', 3)
            ->groupBy('org.id')
            ->groupBy('report.month')->get()->toArray();

        self::insert(array_map(function ($n) {
            return (array) $n;
        }, $re));

        foreach ([3, 2] as $value) {
            $re = DB::table('usr_user_organize as org')
                ->leftJoin('rpt_organize_report_monthly as report', 'org.id', '=', 'report.organize_id')
                ->select(['org.parent as organize_id', 'report.month'])
                ->selectRaw('SUM(report.on_price) as on_price')
                ->selectRaw('SUM(report.on_gross_profit) as on_gross_profit')
                ->selectRaw('SUM(report.off_price) as off_price')
                ->selectRaw('SUM(report.off_gross_profit) as off_gross_profit')
                ->selectRaw('SUM(report.total_price) as total_price')
                ->selectRaw('SUM(report.total_gross_profit) as total_gross_profit')
                ->selectRaw('SUM(report.users) as users')
                ->where('org.level', $value)
                ->whereBetween('report.month', [$sdate, $edate])
                ->groupBy('org.parent')
                ->groupBy('report.month')->get()->toArray();

            self::insert(array_map(function ($n) {
                return (array) $n;
            }, $re));
        }
    }

}
