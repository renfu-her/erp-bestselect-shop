<?php

namespace App\Http\Controllers\Cms\Marketing;

use App\Http\Controllers\Controller;
use App\Models\ProductReport;
use App\Models\RptOrderDailyReport;
use App\Models\RptProductReportDaily;
use App\Models\SaleChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProductReportCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $request->query();
        $cond['year'] = Arr::get($query, 'y', date('Y'));
        $cond['quarter'] = Arr::get($query, 'quarter', intval(ceil(date('n') / 3)));

        $year_range = [];
        for ($i = 2021; $i <= date('Y'); $i++) {
            $year_range[] = $i;
        }

        $product = RptProductReportDaily::dataListCategory($cond['year'], $cond['quarter'])
            ->orderBy('data.gross_profit', 'DESC')->get();
        $re = ProductReport::dataList($cond['year'], $cond['quarter']);

        $salechannelReport = RptOrderDailyReport::dataList($cond['year'], $cond['quarter'])->get();

        return view('cms.reports.product_report.list', [
            'year_range' => $year_range,
            'product' => $product,
            'cond' => $cond,
            'dataList' => $re['seasons'],
            'products' => $re['products'],
            'suppliers' => $re['suppliers'],
            'salechannelReport' => $salechannelReport,
            'SaleChannels' => SaleChannel::get(),
        ]);

    }

    public function RptOrderDailyReport(Request $request)
    {
        $d = $request->all();

        $channel_id = Arr::get($d, 'salechannel_id', null);
        $year = Arr::get($d, 'year', date('Y'));
        $quarter = Arr::get($d, 'quarter', intval(ceil(date('n') / 3)));
       
        return response()->json([
            'status' => '0',
            'data' => RptOrderDailyReport::dataList($year, $quarter, $channel_id)->get(),
        ]);
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
