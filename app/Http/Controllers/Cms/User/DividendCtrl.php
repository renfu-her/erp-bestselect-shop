<?php

namespace App\Http\Controllers\Cms\User;

use App\Enums\Discount\DividendCategory;
use App\Http\Controllers\Controller;
use App\Models\CustomerDividend;
use App\Models\DividendErpLog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class DividendCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
       // dd('aa');

        $titleGet = [];
        $titleUse = [];
        $fieldGet = [];
        $fieldUse = [];
        foreach (DividendCategory::asArray() as $value) {
            $desc = DividendCategory::fromValue($value)->description;
            $titleGet[] = $desc . "取得";
            $fieldGet[] = $value . "_get";
            if ($value == 'order') {
                $titleUse[] = $desc . "使用";
                $fieldUse[] = $value . "_used";
            }
        }

        $query = $request->query();

        $keyword = Arr::get($query, 'keyword');

        $dataList = CustomerDividend::totalList($keyword)->paginate(100)->appends($query);
        CustomerDividend::format($dataList);

        $total = CustomerDividend::getByCategory();
     
        //  dd( CustomerDividend::totalList($keyword)->limit(10)->get()->toArray());
        return view('cms.admin.customer_dividend.list', [
            'dataList' => $dataList,
            'titleGet' => $titleGet,
            'titleUse' => $titleUse,
            'fieldGet' => $fieldGet,
            'fieldUse' => $fieldUse,
            'total' => $total,
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

    public function log($id)
    {
        $dataList = DividendErpLog::dataList()->where('customer.id', $id)->get();
        return view('cms.admin.customer_dividend.log', [
            'dataList' => $dataList,
        ]);
    }

    /*
     * 剩餘點數
     */
    public function remain($category)
    {
        $dataList = CustomerDividend::queryDividendByCategory($category, 'remain')->paginate(100);

        return view('cms.admin.customer_dividend.remain', [
            'dataList' => $dataList,
        ]);
    }

    /**
     * @param $category
     * 使用點數
     */
    public function used($category)
    {
        $dataList = CustomerDividend::usedDividendByCategory($category);

        return view('cms.admin.customer_dividend.used', [
            'dataList' => $dataList,
        ]);
    }

    /**
     * @param $category
     * 發放點數
     */
    public function dividend($category)
    {
        {
            $dataList = CustomerDividend::dividencByCategory($category);

            return view('cms.admin.customer_dividend.dividend', [
                'dataList' => $dataList,
            ]);
        }
    }
}
