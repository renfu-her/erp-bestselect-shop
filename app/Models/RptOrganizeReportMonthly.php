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

    public static function report($date = '2022-09-01')
    {

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
