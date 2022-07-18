<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Http\Controllers\Controller;
use App\Models\OrderCustomerProfitReport;
use App\Models\OrderMonthProfitReport;
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

        $dataList = OrderMonthProfitReport::dataList($cond['keyword'], $cond['report_month'])->paginate($page)
            ->appends($query);
        // dd( OrderProfitReport::dataList()->get());
        return view('cms.commodity.order_bonus.list', [
            'dataList' => $dataList,
            'cond' => $cond,
            'data_per_page' => $page]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('cms.commodity.order_bonus.edit');
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
            'title' => 'required',
            'month' => 'date',
        ]);

        //  dd($_POST);

        $d = $request->all();
        // dd($d);
        $re = OrderMonthProfitReport::createReport($d['title'], $d['month'], $request->user()->id);

        if ($re['success'] == '1') {
            wToast('新增完成');
        } else {
            wToast('無該月份資料',['type'=>'danger']);
        }
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
        OrderMonthProfitReport::deleteReport($id);
        wToast('刪除完成');
        return redirect(route('cms.order-bonus.index'));
    }

    public function detail($id)
    {
        $month_report = OrderMonthProfitReport::where('id', $id)->get()->first();
        if (!$month_report) {
            return abort(404);
        }
        $customer_reports = OrderCustomerProfitReport::dataList($id)->get();
        //   dd($customer_report);
        // $profit = OrderProfit::dataList(null, $report->customer_id, $report->report_at . "/1")->get();

        return view('cms.commodity.order_bonus.detail_list', [
            'customer_reports' => $customer_reports,
            'month_report' => $month_report,
        ]);

    }
}
