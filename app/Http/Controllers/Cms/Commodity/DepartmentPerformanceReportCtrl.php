<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class DepartmentPerformanceReportCtrl extends Controller
{
    //
    public function index(Request $request)
    {

        $query = $request->query();

        $cond['sDate'] = Arr::get($query, 'sDate', date('Y-m-01'));
        $cond['eDate'] = Arr::get($query, 'eDate', date('Y-m-t'));
        $cond['user_id'] = Arr::get($query, 'user_id', []);

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
        /*
        $dataList = RptProductManagerReport::managerList($query['sDate'], $query['eDate'], $cond)->get();

        return view('cms.commodity.product_manager_report.list', [
            'users' => RptProductManagerReport::managers()->get(),
            'year' => $year,
            'cond' => $cond,
            //   'season' => $this->season,
            'dataList' => $dataList,
            'pageTitle' => $pageTitle,
            'query' => $query,
            'search' => true,
        ]);
        */

    }
}
