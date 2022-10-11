<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Http\Controllers\Controller;
use App\Models\RptProductManagerReport;
use Illuminate\Http\Request;

class ProductManagerReportCtrl extends Controller
{
    public $season = [1 => 'ㄧ', 2 => '二', 3 => '三', 4 => '四'];

    public function index(Request $request)
    {
        $query = $request->query();
        $cond = app('App\Http\Controllers\Cms\Commodity\UserPerformanceReportCtrl')->cond($query);

        
        $sYear = 2022;

        $year = [];

        for ($i = 0; $i < Date("Y") - $sYear + 1; $i++) {
            $year[] = $sYear + $i;
        }

        $type = ['year' => "整年度", "season" => "季", "month" => "月份"];

        $pageTitle = $cond['year'] . "年 ";
        switch ($cond['type']) {
            case 'year':
                $pageTitle .= " 年度報表";
                break;
            case 'season':
                $pageTitle .= "第" . $this->season[$cond['season']] . "季 報表";
                break;
            case 'month':
                $pageTitle .= $cond['month'] . "月 報表";
                break;
        }

        $dataList = RptProductManagerReport::managerList($cond['type'], $cond['year'], $cond)->get();

        return view('cms.commodity.product_manager_report.list', [
            'type' => $type,
            'year' => $year,
            'cond' => $cond,
            'season' => $this->season,
            'dataList' => $dataList,
            'pageTitle' => $pageTitle,
            'query' => $query,
            'search' => true,
        ]);

    }

}
