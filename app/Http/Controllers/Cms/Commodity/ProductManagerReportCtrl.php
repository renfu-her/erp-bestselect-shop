<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Http\Controllers\Controller;
use App\Models\RptProductManagerReport;
use App\Models\RptProductReportDaily;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProductManagerReportCtrl extends Controller
{

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

    }
    public function product(Request $request, $user_id)
    {
        $query = $request->query();

        $dataList = RptProductManagerReport::productList($query['sDate'], $query['eDate'], ['user_id' => $user_id])->get();

        $userName = User::where('id', $user_id)->get()->first()->name;
        $pageTitle = $userName . " " . 
            date('Y/m/d', strtotime($query['sDate'])) . " ~ " . 
            date('Y/m/d', strtotime($query['eDate'])) . " 報表";
        // dd($dataList);
        return view('cms.commodity.product_manager_report.product', [
            'dataList' => $dataList,
            'pageTitle' => $pageTitle,
            'query' => $query,
            'prevPage' => route('cms.product-manager-report.index'),
        ]);
    }

    public function renew(Request $request)
    {

        $request->validate(['year' => 'required', 'month' => 'required']);
        $d = $request->all();
        $date = $d['year'] . "-" . $d['month'] . "-01";
        
        RptProductReportDaily::report($date,'month');

        wToast('資料更新完成');

        return redirect(route('cms.product-manager-report.index'));
    }

}
