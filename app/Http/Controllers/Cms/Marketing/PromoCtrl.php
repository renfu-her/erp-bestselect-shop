<?php

namespace App\Http\Controllers\Cms\Marketing;

use App\Enums\Discount\DisCategory;
use App\Enums\Discount\DisMethod;
use App\Enums\Discount\DisStatus;
use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class PromoCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $cond = [];
        $query = $request->query();

        $data_per_page = getPageCount(Arr::get($query, 'data_per_page', 10));
        $cond['title'] = Arr::get($query, 'title');
        $cond['method_code'] = Arr::get($query, 'method_code');
        $cond['status_code'] = Arr::get($query, 'status_code', '');
        $cond['start_date'] = Arr::get($query, 'start_date');
        $cond['end_date'] = Arr::get($query, 'end_date');
        $cond['is_global'] = Arr::get($query, 'is_global');

        //  dd($cond['method_code']);
        $status_code = $cond['status_code'] ? explode(',', $cond['status_code']) : null;

        $dataList = Discount::dataList([DisCategory::coupon()->value, DisCategory::code()->value],
            $status_code,
            $cond['title'],
            $cond['start_date'],
            $cond['end_date'],
            $cond['method_code'],
            $cond['is_global'])->paginate($data_per_page)->appends($query);

        $cond['method_code'] = $cond['method_code'] ? $cond['method_code'] : [];

        return view('cms.marketing.promo.list', [
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
        //

        //   dd(DisCategory::getValueWithDesc(['coupon','code']));
        return view('cms.marketing.promo.edit', [
            'method' => 'create',
            'dis_methods' => DisMethod::getValueWithDesc(['cash', 'percent']),
            'collections' => Collection::select('id', 'name')->get(),
            'formAction' => Route("cms.promo.create"),
            'dis_categorys' => DisCategory::getValueWithDesc(['coupon', 'code']),
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

        $rules = [
            'title' => 'required',
            'method_code' => ['required', Rule::in(array_keys(DisMethod::getValueWithDesc()))],
            'discount_value' => 'required|numeric',
            'min_consume' => 'required|numeric',
        ];

        switch ($_POST['category']) {
            case DisCategory::code()->value:
                $rules['sn'] = ['unique:App\Models\Discount'];
                $rules['max_usage'] = 'numeric';
                break;
        }

        $request->validate($rules);

        $d = $request->all();

        $is_grand_total = isset($d['is_grand_total']) ? $d['is_grand_total'] : '0';
        $method_code = $d['method_code'];

        switch ($d['category']) {
            case DisCategory::coupon()->value:
                Discount::createCoupon($d['title'], $d['min_consume'],
                    DisMethod::$method_code(),
                    $d['discount_value'],
                    $is_grand_total,
                    isset($d['collection_id']) ? $d['collection_id'] : [],
                    $d['life_cycle']);
                break;
            case DisCategory::code()->value:
                Discount::createCode($d['sn'],
                    $d['title'], $d['min_consume'],
                    DisMethod::$method_code(),
                    $d['discount_value'],
                    $d['start_date'],
                    $d['end_date'],
                    $is_grand_total,
                    isset($d['collection_id']) ? $d['collection_id'] : [],
                    $d['max_usage']);
                break;

        }

        wToast('新增完成');

        return redirect(route('cms.promo.index'));
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

        $data = Discount::where('id', $id)->get()->first();
        if (!$data) {
            return abort(404);
        }

        $data->start_date = str_replace(' ', 'T', $data->start_date);
        $data->end_date = str_replace(' ', 'T', $data->end_date);

        $discountCollections = array_map(function ($n) {
            return $n->collection_id;
        }, Discount::getDicountCollections($id)->get()->toArray());

        return view('cms.marketing.promo.edit', [
            'method' => 'edit',
            'breadcrumb_data' => '優惠券',
            'type' => 'coupon/code',
            'data' => $data,
            'dis_methods' => DisMethod::getValueWithDesc(['cash', 'percent']),
            'collections' => Collection::select('id', 'name')->get(),
            'formAction' => Route("cms.discount.edit", ['id' => $id]),
            'dis_categorys' => DisCategory::getValueWithDesc(['coupon', 'code']),
            'discountCollections' => $discountCollections,
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
        $rules = [
            'title' => 'required',
            'method_code' => ['required', Rule::in(array_keys(DisMethod::getValueWithDesc()))],
            'discount_value' => 'required|numeric',
            'min_consume' => 'required|numeric',
            'collection_id' => 'array',
        ];

        switch ($_POST['category']) {
            case DisCategory::code()->value:
                //      $rules['sn'] = ['unique:App\Models\Discount'];
                $rules['max_usage'] = 'numeric';
                break;
        }

        $request->validate($rules);

        $d = $request->all();

        $is_grand_total = isset($d['is_grand_total']) ? $d['is_grand_total'] : '0';
        $method_code = $d['method_code'];

        $is_global = '0';
        if (isset($d['collection_id'])) {
            $is_global = '1';
            Discount::updateDiscountCollection($id, $d['collection_id']);
        }

        $updateData = ['title' => $d['title'],
            'min_consume' => $d['min_consume'],
            'discount_value' => $d['discount_value'],
            'is_grand_total' => $is_grand_total,
            'method_code' => DisMethod::$method_code()->value,
            'is_global' => $is_global,
        ];

        switch ($d['category']) {
            case DisCategory::coupon()->value:
                $updateData['life_cycle'] = $d['life_cycle'];
                Discount::where('id', $id)->update($updateData);
                break;
            case DisCategory::code()->value:
                $updateData['start_date'] = $d['start_date'];
                $updateData['end_date'] = $d['end_date'];
                $updateData['max_usage'] = $d['max_usage'];
                Discount::where('id', $id)->update($updateData);
                break;

        }

        //  if(isset($d['collection_id']) ? $d['collection_id'] : [],

        wToast('修改完成');

        return redirect(route('cms.promo.index'));
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
        Discount::delProcess($id);
        wToast('刪除完成');
        return redirect(route('cms.promo.index'));
    }
}
