<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Addr;
use App\Models\Collection;
use App\Models\CustomerReportDaily;
use App\Models\CustomerReportMonth;
use App\Models\OrderReportDaily;
use App\Models\OrderReportMonth;
use Illuminate\Http\Request;

class DashboardCtrl extends Controller
{
    //
    public function __invoke(Request $request)
    {

        // Discount::createDiscount('a013', DisMethod::fromKey('cash'), 200, '2020/01/05', '2020/02/05', 1);
        $citys = Addr::getCitys();

        $reportDaily = OrderReportDaily::where('date', Date('Y-m-d'))->get()->first();

        $reportMonth = OrderReportMonth::where('date', Date('Y-m-1'))->get()->first();
        $reportPrevMonth = OrderReportMonth::where('date', Date('Y-m-1', strtotime("-1 months")))->get()->first();

        $customerDaily = CustomerReportDaily::dataList()->where('daily.date', Date('Y-m-d'))->limit(20)->get()->toArray();
        $customerPrevMonth = CustomerReportMonth::dataList()->where('month.date', Date('Y-m-1', strtotime("-1 months")))->limit(20)->get()->toArray();

        $reportUpdatedTime = CustomerReportDaily::orderBy('updated_at', "DESC")->get()->first();
        $reportUpdatedTime = $reportUpdatedTime ? date('Y/m/d H:i', strtotime($reportUpdatedTime->updated_at)) : '';

        $topCollections = Collection::where('erp_top', 1)->get()->toArray();
        $topCollections = array_map(function ($n) {
            return ['url' => frontendUrl() . "collection/${n['id']}/${n['name']}",
                'name' => $n['name']];
        }, $topCollections);

        $regions = Addr::getRegions($citys[0]['city_id']);
        return view('cms.dashboard', [
            'citys' => $citys,
            'regions' => $regions,
            'reportDaily' => $reportDaily,
            'reportMonth' => $reportMonth,
            'reportPrevMonth' => $reportPrevMonth,
            'topCollections' => $topCollections,
            'customerDaily' => $customerDaily,
            'customerPrevMonth' => $customerPrevMonth,
            'reportUpdatedTime' => $reportUpdatedTime,
        ]);

    }

}
