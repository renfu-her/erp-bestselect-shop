<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\SaleChannel;
use Illuminate\Http\Request;

class SaleChannelCtrl extends Controller
{

    public function index(Request $request)
    {
        $query = $request->query();
        $dataList = SaleChannel::saleList()->orderBy('is_master', 'DESC')->paginate(10)->appends($query);
        // dd(SaleChannel::saleList()->get()->toArray());
        return view('cms.settings.sale_channel.list', [
            'dataList' => $dataList,
        ]);
    }

    public function create(Request $request)
    {
        return view('cms.settings.sale_channel.edit', [
            'method' => 'create',
            'formAction' => Route('cms.sale_channel.create'),
        ]);
    }

    public function store(Request $request)
    {
        $query = $request->query();
        $this->validInputValue($request);
        $v = $this->getInputValue($request);
        $id = SaleChannel::create([
            'title' => $v['title'],
            'contact_person' => $v['contact_person'],
            'contact_tel' => $v['contact_tel'],
            'chargeman' => $v['chargeman'],
            'sales_type' => $v['sales_type'],
            'use_coupon' => $v['use_coupon'],
            'is_realtime' => $v['is_realtime'],
            'discount' => $v['discount'],
            'dividend_limit' => $v['dividend_limit'],
            'dividend_rate' => $v['dividend_rate'],
            'event_dividend_rate' => $v['event_dividend_rate'],
            'event_sdate' => $v['event_sdate'],
            'event_edate' => $v['event_edate'],
        ]);
        wToast(__('Add finished.'));
        return redirect(Route('cms.sale_channel.edit', [
            'id' => $id,
            'query' => $query,
        ]));
    }

    //驗證資料
    private function validInputValue(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'contact_person' => 'required|string',
            'contact_tel' => 'required|string',
            'chargeman' => 'required|string',
            'sales_type' => 'required|numeric',
            'use_coupon' => 'required|numeric',
            'discount' => 'required|numeric',
            'dividend_limit' => 'required|numeric',
            'dividend_rate' => 'required|numeric',
            'event_dividend_rate' => 'required|numeric',
            'event_sdate' => 'date|nullable',
            'event_edate' => 'date|nullable',
        ]);
    }

    //取得欄位資料
    private function getInputValue(Request $request)
    {
        return $request->only('title',
            'contact_person',
            'contact_tel',
            'chargeman',
            'sales_type',
            'use_coupon',
            'is_realtime',
            'discount',
            'dividend_limit',
            'dividend_rate',
            'event_dividend_rate',
            'event_sdate',
            'event_edate');
    }

    public function edit(Request $request, $id)
    {
        $data = SaleChannel::where('id', '=', $id)->first();

        if (!$data) {
            return abort(404);
        }
        return view('cms.settings.sale_channel.edit', [
            'id' => $id,
            'data' => $data,
            'method' => 'edit',
            'formAction' => Route('cms.sale_channel.edit', ['id' => $id]),
        ]);
    }

    public function update(Request $request, $id)
    {
        $query = $request->query();
        $this->validInputValue($request);
        $v = $this->getInputValue($request);

        SaleChannel::where('id', '=', $id)->update($v);
        wToast(__('Edit finished.'));
        return redirect(Route('cms.sale_channel.edit', [
            'id' => $id,
            'query' => $query,
        ]));
    }

    public function destroy(Request $request, $id)
    {
        SaleChannel::where('id', '=', $id)->delete();
        wToast(__('Delete finished.'));
        return redirect(Route('cms.sale_channel.index'));
    }

    public function batchPrice(Request $request, $id)
    {
        SaleChannel::batchPrice($id);
        wToast(__('Edit finished.'));
        return redirect(Route('cms.sale_channel.index'));

    }
}
