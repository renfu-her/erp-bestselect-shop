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
        $cond = self::cond($query);

        $sYear = 2022;

        $year = [];

        for ($i = 0; $i < Date("Y") - $sYear + 1; $i++) {
            $year[] = $sYear + $i;
        }

        $dataList = RptOrganizeReportMonthly::dataList($cond['type'], $cond['year'], ['level' => 2,
            'season' => $cond['season'],
            'month' => $cond['month'],
            'department' => $cond['department']])
            ->get();

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

        return view('cms.commodity.user_performance_report.list', [
            'department' => UserOrganize::where('level', 2)->get(),
            'type' => $type,
            'year' => $year,
            'cond' => $cond,
            'season' => $this->season,
            'dataList' => $dataList,
            'pageTitle' => $pageTitle,
            'query' => $query,
            'search' => true,
            'targetType' => 'department',

        ]);

    }

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

    public function department(Request $request, $organize_id)
    {
        $query = $request->query();
        $cond = self::cond($query);

        $dataList = RptOrganizeReportMonthly::dataList($cond['type'], $cond['year'], [
            'season' => $cond['season'],
            'month' => $cond['month'],
            'parent' => $organize_id])
            ->get();

        return view('cms.commodity.user_performance_report.list', [
            'dataList' => $dataList,
            'pageTitle' => self::pageTitle($cond, $organize_id),
            'query' => $query,
            'targetType' => 'group',
            'cond' => $cond,
            'prevPage' => route('cms.user-performance-report.index', $cond),

        ]);
    }

    public function group(Request $request, $organize_id)
    {
        $query = $request->query();
        $cond = self::cond($query);

        $dataList = RptUserReportMonthly::dataList($cond['type'], $cond['year'], [
            'season' => $cond['season'],
            'month' => $cond['month'],
            'group' => $organize_id])
            ->get();

        $organize_parent_id = UserOrganize::where('id', $organize_id)->get()->first()->parent;

        return view('cms.commodity.user_performance_report.list', [
            'dataList' => $dataList,
            'pageTitle' => self::pageTitle($cond, $organize_id),
            'query' => $query,
            'targetType' => 'user',
            'cond' => $cond,
            'prevPage' => route('cms.user-performance-report.department', array_merge($cond, ['organize_id' => $organize_parent_id])),
        ]);
    }

    public function user(Request $request, $user_id)
    {
        $query = $request->query();
        $cond = self::cond($query);

        $dataList = RptUserReportMonthly::userOrder($cond['type'], $cond['year'], [
            'season' => $cond['season'],
            'month' => $cond['month'],
            'user_id' => $user_id])
            ->orderBy('sale_channel.sales_type')
            ->get();

        $user = User::where('id', $user_id)->withTrashed()->get()->first();

        $organize_id = UserOrganize::where('level', 3)->where('title', $user->group)->get()->first()->id;

        return view('cms.commodity.user_performance_report.order_list', [
            'dataList' => $dataList,
            'pageTitle' => $user->name . " " . self::pageTitle($cond),
            'query' => $query,
            'cond' => $cond,
            'prevPage' => route('cms.user-performance-report.group', array_merge($cond, ['organize_id' => $organize_id])),
        ]);

    }

    public function renew(Request $request)
    {
        $request->validate(['year' => 'required', 'month' => 'required']);
        $d = $request->all();
        $date = $d['year'] . "-" . $d['month'];
        //   RptUserReportMonthly::grossProfit();
        RptUserReportMonthly::report($date);
        RptOrganizeReportMonthly::report($date);

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
