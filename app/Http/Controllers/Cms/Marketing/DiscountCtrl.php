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

class DiscountCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        //  Discount::dataList();
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
     //   dd( $cond['method_code']);
        $dataList = Discount::dataList(DisCategory::normal(),
            $status_code,
            $cond['title'],
            $cond['start_date'],
            $cond['end_date'],
            $cond['method_code'])->paginate($data_per_page)->appends($query);
       
        //   $cond['status_code'] = $cond['status_code'] ? explode(',', $cond['status_code']) : [];
        $cond['method_code'] = $cond['method_code']?$cond['method_code']:[];
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
            //  'start_date' => 'required|date',
            //  'end_date' => 'required|date',
            'discount_value' => 'required|numeric',
            'min_consume' => 'required|numeric',
        ]);

        $d = $request->all();
        $is_grand_total = isset($d['is_grand_total']) ? $d['is_grand_total'] : '0';
        $method_code = $d['method_code'];

        Discount::createDiscount($d['title'],
            $d['min_consume'],
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
    public function edit(Request $request, $id)
    {
        //

        $data = Discount::where('id', $id)->get()->first();
        if (!$data) {
            return abort(404);
        }

        $data->start_date = str_replace(' ', 'T', $data->start_date);
        $data->end_date = str_replace(' ', 'T', $data->end_date);

        //dd($data->start_date);
        // dd($data);
        return view('cms.marketing.discount.edit', [
            'method' => 'edit',
            'breadcrumb_data' => '現折優惠',
            'data' => $data,
            'dis_methods' => DisMethod::getValueWithDesc(),
            'collections' => Collection::select('id', 'name')->get(),
            'formAction' => Route("cms.discount.edit", ['id' => $id]),

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
        $request->validate([
            'title' => 'required',
            'method_code' => ['required', Rule::in(array_keys(DisMethod::getValueWithDesc()))],
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'discount_value' => 'required|numeric',
            'min_consume' => 'required|numeric',
        ]);
        $d = request()->all();
        $is_global = 1;

        if (isset($d['collection_id']) && count($d['collection_id']) > 0) {
            Discount::updateDiscountCollection($id, $d['collection_id']);
            $is_global = 0;
        }

        Discount::where('id', $id)->update(
            [
                'title' => $d['title'],
                'start_date' => $d['start_date'],
                'end_date' => $d['end_date'],
                'discount_value' => $d['discount_value'],
                'method_code' => $d['method_code'],
                'min_consume' => $d['min_consume'],
                'is_global' => $is_global,
            ]
        );

        wToast('更新完成');
        return redirect(route('cms.discount.index'));
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
        return redirect(route('cms.discount.index'));

    }
}
