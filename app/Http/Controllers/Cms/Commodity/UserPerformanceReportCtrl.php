<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Http\Controllers\Controller;
use App\Models\RptOrganizeReportMonthly;
use App\Models\RptUserReportMonthly;
use App\Models\User;
use App\Models\UserOrganize;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class UserPerformanceReportCtrl extends Controller
{
    public $season = [1 => 'ㄧ', 2 => '二', 3 => '三', 4 => '四'];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $query = $request->query();
        //  $cond = self::cond($query);

        $cond['sDate'] = Arr::get($query, 'sDate', date('Y-m-01'));
        $cond['eDate'] = Arr::get($query, 'eDate', date('Y-m-t'));
        $cond['department'] = Arr::get($query, 'department', []);


        $cond['sDate'] = $cond['sDate'] ? $cond['sDate'] : date('Y-m-01');
        $cond['eDate'] = $cond['eDate'] ? $cond['eDate'] : date('Y-m-t');

        $dataList = RptOrganizeReportMonthly::dataList($cond['sDate'], $cond['eDate'], ['level' => 2,
            'department' => $cond['department']])
            ->get();

        $sYear = 2022;
        $year = [];
        for ($i = 0; $i < Date("Y") - $sYear + 1; $i++) {
            $year[] = $sYear + $i;
        }

        // $pageTitle = date("Y",strtotime($cond['sdate'])) . "年 ";
        $pageTitle = date('Y/m/d', strtotime($cond['sDate'])) . " ~ " . 
            date('Y/m/d', strtotime($cond['eDate'])) . " 報表";
        $cond['year'] = date("Y", strtotime($cond['sDate']));
        $cond['month'] = date("m", strtotime($cond['sDate']));
        $query['sDate'] = $cond['sDate'];
        $query['eDate'] = $cond['eDate'];
        /*
        switch ($cond['type']) {
        case 'year':
        $pageTitle .= " 年度報表";
        break;
        case 'season':
        $pageTitle .= "第" . $this->season[$cond['season']] . "季 報表";
        break;
        case 'month':
        $pageTitle .=  date("Y",strtotime($cond['sdate'])) . "月 報表";
        break;
        }
         */

        return view('cms.commodity.user_performance_report.list', [
            'department' => UserOrganize::where('level', 2)->get(),
            // 'type' => $type,
            'year' => $year,
            'cond' => $cond,
            //  'season' => $this->season,
            'dataList' => $dataList,
            'pageTitle' => $pageTitle,
            'query' => $query,
            'search' => true,
            'targetType' => 'department',

        ]);

    }
    /*
    public function cond($query)
    {
    $sYear = 2022;
    $_s = 12 / date("n");

    if ($_s >= 4) {
    $currentSeason = 1;
    } else if ($_s <= 1.2) {
    $currentSeason = 4;
    } else {
    if ($_s >= 2) {
    $currentSeason = 2;
    } else {
    $currentSeason = 3;
    }
    }

    $cond = [];
    $cond['department'] = Arr::get($query, 'department', []);
    $cond['type'] = Arr::get($query, 'type', 'year');
    $cond['year'] = Arr::get($query, 'year', $sYear);
    $cond['month'] = Arr::get($query, 'month', date("n"));
    $cond['season'] = Arr::get($query, 'season', $currentSeason);

    return $cond;

    }
     */
    /*
        public function pageTitle($cond, $organize_id = null)
        {
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

        if ($organize_id) {
        $otitle = UserOrganize::where('id', $organize_id)->get()->first()->title;
        $pageTitle = $otitle . " " . $pageTitle;
        }
        return $pageTitle;

        }
    */

    public function department(Request $request, $organize_id)
    {
        $query = $request->query();

        $dataList = RptOrganizeReportMonthly::dataList($query['sDate'], $query['eDate'], [
            'parent' => $organize_id])
            ->get();

        $pageTitle = date('Y/m/d', strtotime($query['sDate'])) . " ~ " . 
            date('Y/m/d', strtotime($query['eDate'])) . " " . 
            UserOrganize::where('id', $organize_id)->get()->first()->title;

        return view('cms.commodity.user_performance_report.list', [
            'dataList' => $dataList,
            'pageTitle' => $pageTitle,
            'query' => $query,
            'targetType' => 'group',
            'cond' => $query,
            'prevPage' => route('cms.user-performance-report.index', $query),

        ]);
    }

    public function group(Request $request, $organize_id)
    {
        $query = $request->query();

        $dataList = RptUserReportMonthly::dataList($query['sDate'], $query['eDate'], [
            'group' => $organize_id])
            ->get();

        $organize_parent_id = UserOrganize::where('id', $organize_id)->get()->first()->parent;
        $pageTitle = date('Y/m/d', strtotime($query['sDate'])) . " ~ " . 
            date('Y/m/d', strtotime($query['eDate'])) . " " . 
            UserOrganize::where('id', $organize_id)->get()->first()->title;

        return view('cms.commodity.user_performance_report.list', [
            'dataList' => $dataList,
            'pageTitle' => $pageTitle,
            'query' => $query,
            'targetType' => 'user',
            'cond' => $query,
            'prevPage' => route('cms.user-performance-report.department', array_merge($query, ['organize_id' => $organize_parent_id])),
        ]);
    }

    public function user(Request $request, $user_id)
    {
        $query = $request->query();

        $dataList = RptUserReportMonthly::userOrder($query['sDate'], $query['eDate'], [
            'user_id' => $user_id])
            ->orderBy('sale_channel.sales_type')
            ->get();

        $user = User::where('id', $user_id)->withTrashed()->get()->first();

        $organize_id = UserOrganize::where('level', 3)->where('title', $user->group)->get()->first()->id;

        $pageTitle = $user->name . " " . date('Y/m/d', strtotime($query['sDate'])) . " ~ " . 
            date('Y/m/d', strtotime($query['eDate'])) . " " . 
            UserOrganize::where('id', $organize_id)->get()->first()->title;

        return view('cms.commodity.user_performance_report.order_list', [
            'dataList' => $dataList,
            'pageTitle' => $pageTitle,
            'query' => $query,
            'prevPage' => route('cms.user-performance-report.group', array_merge($query, ['organize_id' => $organize_id])),
        ]);

    }

    public function renew(Request $request)
    {
        $request->validate(['year' => 'required', 'month' => 'required']);
        $d = $request->all();
        $date = $d['year'] . "-" . $d['month'];
        //   RptUserReportMonthly::grossProfit();
        RptUserReportMonthly::report($date, 'month');
        RptOrganizeReportMonthly::report($date, 'month');

        wToast('資料更新完成');

        return redirect(route('cms.user-performance-report.index'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
