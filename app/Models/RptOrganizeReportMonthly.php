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

    public static function dataList($type = null, $year = null, $options = [])
    {

        $sub = DB::table('rpt_organize_report_monthly')
            ->select(['organize_id'])
            ->selectRaw('SUM(on_price) as on_price')
            ->selectRaw('SUM(on_gross_profit) as on_gross_profit')
            ->selectRaw('SUM(off_price) as off_price')
            ->selectRaw('SUM(off_gross_profit) as off_gross_profit')
            ->selectRaw('SUM(total_price) as total_price')
            ->selectRaw('SUM(total_gross_profit) as total_gross_profit')
            ->selectRaw('SUM(users) as users');

        switch ($type) {
            case 'year':
                $sdate = date("Y-01-01", strtotime($year));
                $edate = date("Y-12-31", strtotime($year));
                break;
            case 'month':
                if (isset($options['month'])) {
                    $sdate = date("Y-" . $options['month'] . "-01", strtotime($year));
                    $edate = date("Y-" . $options['month'] . "-t", strtotime($year));
                }
                break;
            case 'season':
                if (isset($options['season'])) {
                    switch ($options['season']) {
                        case '1':
                            $_m = [1, 3];
                            break;
                        case '2':
                            $_m = [4, 6];
                            break;
                        case '3':
                            $_m = [7, 9];
                            break;
                        case '4':
                            $_m = [10, 12];
                            break;
                    }

                    $sdate = date("Y-$_m[0]-01", strtotime($year));
                    $edate = date("Y-$_m[1]-t", strtotime($year));

                }
                break;

        }
        $sub->whereBetween('month', [$sdate, $edate])
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

    public static function report($date = null)
    {
        $date = $date ? $date : Date("Y-m-01 00:00:00", strtotime(date('Y-m-d') . " -1 day"));

        $currentMonth = Date("Y-m-01", strtotime($date));
        self::where('month', $currentMonth)->delete();
        // 三階
        $re = DB::table('usr_user_organize as org')
            ->leftJoin('usr_users as user', 'org.title', '=', 'user.group')
            ->leftJoin('rpt_user_report_monthly as report', 'user.id', '=', 'report.user_id')
            ->select(['org.id as organize_id'])
            ->selectRaw('SUM(on_price) as on_price')
            ->selectRaw('SUM(on_gross_profit) as on_gross_profit')
            ->selectRaw('SUM(off_price) as off_price')
            ->selectRaw('SUM(off_gross_profit) as off_gross_profit')
            ->selectRaw('SUM(total_price) as total_price')
            ->selectRaw('SUM(total_gross_profit) as total_gross_profit')
            ->selectRaw('COUNT(user.id) as users')
            ->where('report.month', $currentMonth)
            ->where('org.level', 3)
            ->groupBy('org.id')->get()->toArray();

        self::insert(array_map(function ($n) use ($currentMonth) {
            $n->month = $currentMonth;
            return (array) $n;
        }, $re));

        foreach ([3, 2] as $value) {
            $re = DB::table('usr_user_organize as org')
                ->leftJoin('rpt_organize_report_monthly as report', 'org.id', '=', 'report.organize_id')
                ->select(['org.parent as organize_id'])
                ->selectRaw('SUM(report.on_price) as on_price')
                ->selectRaw('SUM(report.on_gross_profit) as on_gross_profit')
                ->selectRaw('SUM(report.off_price) as off_price')
                ->selectRaw('SUM(report.off_gross_profit) as off_gross_profit')
                ->selectRaw('SUM(report.total_price) as total_price')
                ->selectRaw('SUM(report.total_gross_profit) as total_gross_profit')
                ->selectRaw('SUM(report.users) as users')
                ->where('org.level', $value)
                ->where('report.month', $currentMonth)
                ->groupBy('org.parent')->get()->toArray();

            self::insert(array_map(function ($n) use ($currentMonth) {
                $n->month = $currentMonth;
                return (array) $n;
            }, $re));
        }
    }

}
