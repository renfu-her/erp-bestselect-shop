<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Http\Controllers\Controller;
use App\Models\OrderProfit;
use App\Models\OrderProfitReport;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class OrderBonusCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //

        $query = $request->query();
        //   dd($query);
        $cond = [];
        $page = getPageCount(Arr::get($query, 'data_per_page', 10));
        $cond['report_month'] = Arr::get($query, 'report_month');
        $cond['check_status'] = Arr::get($query, 'check_status', 'all');
        $cond['keyword'] = Arr::get($query, 'keyword');

        $dataList = OrderProfitReport::dataList($cond['keyword'], $cond['report_month'], $cond['check_status'])->paginate($page)
            ->appends($query);
        // dd( OrderProfitReport::dataList()->get());
        return view('cms.commodity.order_bonus.list', [
            'dataList' => $dataList,
            'cond' => $cond,
            'data_per_page' => $page,
            'check_status' => ['all' => '不限', '0' => '未確認', '1' => '已確認']]);
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
        $request->validate([
            'date' => 'date',
        ]);

        $date = $request->input('date');
        OrderProfitReport::createMonthReport($date);
        wToast('新增完成');
        return redirect(route('cms.order-bonus.index'));
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
        OrderProfitReport::where('id', $id)->whereNull('checked_at')->delete();
        wToast('新增完成');
        return redirect(route('cms.order-bonus.index'));
    }

    public function detail($id)
    {
        $report = OrderProfitReport::dataList()->where('report.id', $id)->get()->first();

        $profit = OrderProfit::dataList(null, $report->customer_id, $report->report_at . "/1")->get();
        
    
        return view('cms.commodity.order_bonus.detail', [
            'dataList' => $profit,
            'report'=>$report
        ]);

    }
}
