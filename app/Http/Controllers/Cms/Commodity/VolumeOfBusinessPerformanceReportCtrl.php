<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Exports\Report\UserPerformanceExport;
use App\Http\Controllers\Controller;
use App\Models\RptUserPerformanceReport;
use App\Models\UserOrganize;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Facades\Excel;

class VolumeOfBusinessPerformanceReportCtrl extends Controller
{
    //
    public function index(Request $request)
    {

        $query = $request->query();

        $cond['sDate'] = Arr::get($query, 'sDate', date('Y-m-01'));
        $cond['eDate'] = Arr::get($query, 'eDate', date('Y-m-t'));
        $cond['department'] = Arr::get($query, 'department', []);

        $sYear = 2022;

        $year = [];

        $cond['sDate'] = $cond['sDate'] ? $cond['sDate'] : date('Y-m-01');
        $cond['eDate'] = $cond['eDate'] ? $cond['eDate'] : date('Y-m-t');

        for ($i = 0; $i < Date("Y") - $sYear + 1; $i++) {
            $year[] = $sYear + $i;
        }

        $pageTitle = date('Y/m/d', strtotime($cond['sDate'])) . " ~ " . date('Y/m/d', strtotime($cond['eDate'])) . " 報表";
        $cond['year'] = date("Y", strtotime($cond['sDate']));
        $cond['month'] = date("m", strtotime($cond['sDate']));
        $query['sDate'] = $cond['sDate'];
        $query['eDate'] = $cond['eDate'];

        $dataList = RptUserPerformanceReport::dataList($query['sDate'], $query['eDate'], [
            'department' => $cond['department'],
        ])->get();

        // $dataList = RptProductManagerReport::managerList($query['sDate'], $query['eDate'], $cond)->get();

        return view('cms.commodity.vob_performance_report.list', [
            'department' => UserOrganize::where('level', 2)->get(),
            'year' => $year,
            'cond' => $cond,
            //   'season' => $this->season,
            'dataList' => $dataList,
            'pageTitle' => $pageTitle,
            'query' => $query,
            'search' => true,
            'targetType' => null,
        ]);

    }

    public function renew(Request $request)
    {

        $request->validate(['year' => 'required', 'month' => 'required']);
        $d = $request->all();
        $date = $d['year'] . "-" . $d['month'];

        RptUserPerformanceReport::report($date, 'month');

        wToast('資料更新完成');

        return redirect(route('cms.vob-performance-report.index'));
    }

    public function exportExcel(Request $request)
    {
        //   dd($request->query());
        $query = $request->query();

        $options = [];
        if (isset($query['department'])) {
            $options['department'] = $query['department'];
        }
        $fname = $query['sDate'] . "_" . $query['eDate'] . "_user_performance_report.xlsx";
        return Excel::download(new UserPerformanceExport($query['sDate'], $query['eDate'], $options), $fname);

    }

}
