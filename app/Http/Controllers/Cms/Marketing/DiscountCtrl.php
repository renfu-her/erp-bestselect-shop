<?php

namespace App\Http\Controllers\Cms\Marketing;

use App\Enums\Discount\DisMethod;
use App\Enums\Discount\DisStatus;
use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class DiscountCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        Discount::dataList();
        $cond = [];
        $query = $request->query();

        $data_per_page = getPageCount(Arr::get($query, 'data_per_page', 10));
        $cond['title'] = Arr::get($query, 'title', 10);
        $cond['method_code'] = Arr::get($query, 'method_code', []);
        $cond['status_code'] = Arr::get($query, 'status_code');
        $cond['start_date'] = Arr::get($query, 'start_date');
        $cond['end_date'] = Arr::get($query, 'end_date');
        $cond['is_global'] = Arr::get($query, 'is_global');

        $dataList = Discount::dataList()->paginate($data_per_page)->appends($query);
        //   $cond['status_code'] = $cond['status_code'] ? explode(',', $cond['status_code']) : [];
        return view('cms.marketing.discount.list', [
            'dataList' => [],
            'dis_methods' => DisMethod::getValueWithDesc(),
            'dis_status' => DisStatus::getValueWithDesc(),
            'data_per_page' => $data_per_page,
            'cond' => $cond,
            'dataList' => $dataList,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // dd('aa');
        //
        return view('cms.marketing.discount.edit', [
            'method' => 'create',
            'dis_methods' => DisMethod::getValueWithDesc(),
            'collections' => Collection::select('id', 'name')->get(),
            'formAction' => Route("cms.discount.create"),
        ]);
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

       // dd($_POST);
        $request->validate([
            'title' => 'required',
            'method_code' => ['required', Rule::in(array_keys(DisMethod::getValueWithDesc()))],
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'discount_value' => 'required|numeric',
            'min_consume' => 'required|numeric',
        ]);

        $d = $request->all();
        $is_grand_total = isset($d['is_grand_total']) ? $d['is_grand_total'] : '0';
        $method_code = $d['method_code'];

        Discount::createDiscount($d['title'],
            DisMethod::$method_code(),
            $d['discount_value'],
            $d['start_date'],
            $d['end_date'],
            $is_grand_total,
            isset($d['collection_id']) ? $d['collection_id'] : []
        );

        wToast('新增完成');
        return redirect(route('cms.discount.index'));
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
        return view('cms.marketing.discount.edit', [
            'method' => 'edit',
            'breadcrumb_data' => '現折優惠',
        ]);
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
