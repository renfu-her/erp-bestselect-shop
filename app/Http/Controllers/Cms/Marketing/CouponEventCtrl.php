<?php

namespace App\Http\Controllers\Cms\Marketing;

use App\Http\Controllers\Controller;
use App\Models\CouponEvent;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CouponEventCtrl extends Controller
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
     

        $dataList = CouponEvent::dataList($cond['title'])->paginate($data_per_page)->appends($query);

        return view('cms.marketing.coupon_event.list', [
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
    public function create(Request $request)
    {
        $coupons = Discount::where('category_code', 'coupon')->get();

        return view('cms.marketing.coupon_event.edit', [
            'method' => 'create',
            'formAction' => Route("cms.coupon-event.create"),
            'coupons' => $coupons,
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
        $request->validate([
            'title' => 'required',
            'sn' => 'required|unique:App\Models\CouponEvent,sn',
            //  'start_date' => 'required|date',
            //  'end_date' => 'required|date',
            'qty_per_once' => 'required|numeric',
            'qty_limit' => 'required|numeric',
            'discount_id' => 'required',
        ]);

        $d = $request->all();

        $reuse = isset($d['reuse']) ? 1 : 0;

        $start_date = !isset($d['start_date']) ? date("Y-m-d H:i:00") : $d['start_date'];
        $end_date = !isset($d['end_date']) ? date("Y-m-d H:i:00", strtotime(now() . " +20 years")) : $d['end_date'];

        CouponEvent::create([
            'title' => $d['title'],
            'sn' => $d['sn'],
            'qty_per_once' => $d['qty_per_once'],
            'qty_limit' => $d['qty_limit'],
            'discount_id' => $d['discount_id'],
            'start_date' => $start_date,
            'end_date' => $end_date,
            'reuse' => $reuse,

        ]);

        wToast('新增完成');
        return redirect(route('cms.coupon-event.index'));
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
        $data = CouponEvent::where('id', $id)->get()->first();
        if (!$data) {
            return abort(404);
        }

        $coupons = Discount::where('category_code', 'coupon')->get();

        return view('cms.marketing.coupon_event.edit', [
            'method' => 'edit',
            'formAction' => Route("cms.coupon-event.edit", ['id' => $id]),
            'coupons' => $coupons,
            'data' => $data,
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
            'sn' => 'required|unique:App\Models\CouponEvent,sn,' . $id,
            'qty_per_once' => 'required|numeric',
            'qty_limit' => 'required|numeric',
            'discount_id' => 'required',
        ]);

        $d = $request->all();

        $reuse = isset($d['reuse']) ? 1 : 0;

        $start_date = !isset($d['start_date']) ? date("Y-m-d H:i:00") : $d['start_date'];
        $end_date = !isset($d['end_date']) ? date("Y-m-d H:i:00", strtotime(now() . " +20 years")) : $d['end_date'];

        CouponEvent::where('id', $id)->update([
            'title' => $d['title'],
            'sn' => $d['sn'],
            'qty_per_once' => $d['qty_per_once'],
            'qty_limit' => $d['qty_limit'],
            'discount_id' => $d['discount_id'],
            'start_date' => $start_date,
            'end_date' => $end_date,
            'reuse' => $reuse,
        ]);

        wToast('更新完成');
        return redirect(route('cms.coupon-event.index'));
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
        CouponEvent::where('id', $id)->delete();
        wToast('刪除完成');
        return redirect(route('cms.coupon-event.index'));
        
    }
}
