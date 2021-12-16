<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SupplierCtrl extends Controller
{

    public function index(Request $request)
    {
        //
        $query = $request->query();
        $title = Arr::get($query, 'title', '');
        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;

        $dataList =  Supplier::getSupplierList($title)
            ->paginate($data_per_page)->appends($query);

        return view('cms.supplier.list', [
            'dataList' => $dataList,
            'title' => $title,
            'data_per_page' => $data_per_page,
        ]);
    }

    public function create(Request $request)
    {
        return view('cms.supplier.edit', [
            'method' => 'create',
            'formAction' => Route('cms.supplier.create'),
        ]);
    }

    public function store(Request $request)
    {
        $query = $request->query();
        $this->validInputValue($request);
        $v = $this->getInputValue($request);
        $id = Supplier::create([
            'name' => $v['name'],
            'nickname' => $v['nickname'],
            'vat_no' => $v['vat_no'],
            'chargeman' => $v['chargeman'],
            'bank_cname' => $v['bank_cname'],
            'bank_code' => $v['bank_code'],
            'bank_acount' => $v['bank_acount'],
            'bank_numer' => $v['bank_numer'],
            'contact_tel' => $v['contact_tel'],
            'contact_address' => $v['contact_address'],
            'contact_person' => $v['contact_person'],
            'email' => $v['email'],
            'memo' => $v['memo'],
        ]);
        wToast(__('Add finished.'));
        return redirect(Route('cms.supplier.edit', [
            'id' => $id,
            'query' => $query
        ]));
    }

    //驗證資料
    private function validInputValue(Request $request) {
        $request->validate([
            'name' => 'required|string',
            'vat_no' => 'required|string',
            'chargeman' => 'required|string',
            'bank_cname' => 'required|string',
            'bank_code' => 'required|string',
            'bank_acount' => 'required|string',
            'bank_numer' => 'required|string',
            'contact_tel' => 'required|string',
            'contact_address' => 'required|string',
            'contact_person' => 'required|string',
            'email' => 'required|string',
        ]);
    }

    //取得欄位資料
    private function getInputValue(Request $request) {
        return $request->only('name', 'nickname', 'vat_no', 'chargeman'
            , 'bank_cname', 'bank_code', 'bank_acount', 'bank_numer'
            , 'contact_tel', 'contact_address', 'contact_person', 'email', 'memo');
    }

    public function edit(Request $request,$id)
    {
        $data = Supplier::where('id', '=', $id)->first();

        if (!$data) {
            return abort(404);
        }
        return view('cms.supplier.edit', [
            'id' => $id,
            'data' => $data,
            'method' => 'edit',
            'formAction' => Route('cms.supplier.edit', ['id' => $id]),
        ]);
    }

    public function update(Request $request, $id)
    {
        $query = $request->query();
        $this->validInputValue($request);
        $v = $this->getInputValue($request);

        Supplier::where('id', '=', $id)->update($v);
        wToast(__('Edit finished.'));
        return redirect(Route('cms.supplier.edit', [
            'id' => $id,
            'query' => $query
        ]));
    }

    public function destroy(Request $request, $id)
    {
        Supplier::where('id', '=', $id)->delete();
        wToast(__('Delete finished.'));
        return redirect(Route('cms.supplier.index'));
    }
}
