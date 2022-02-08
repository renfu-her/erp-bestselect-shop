<?php

namespace App\Http\Controllers\Cms;

use App\Enums\Supplier\Payment;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierPayment;
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

        return view('cms.settings.supplier.list', [
            'dataList' => $dataList,
            'title' => $title,
            'data_per_page' => $data_per_page,
        ]);
    }

    public function create(Request $request)
    {
        return view('cms.settings.supplier.edit', [
            'method' => 'create',
            'formAction' => Route('cms.supplier.create'),
        ]);
    }

    public function store(Request $request)
    {
        $query = $request->query();
        $this->validInputValue($request);
        $paramReq_supplier = $this->getInputValue($request);
        $id = Supplier::create([
            'name' => $paramReq_supplier['name'],
            'nickname' => $paramReq_supplier['nickname'],
            'vat_no' => $paramReq_supplier['vat_no'],
            'contact_tel' => $paramReq_supplier['contact_tel'],
            'contact_address' => $paramReq_supplier['contact_address'],
            'contact_person' => $paramReq_supplier['contact_person'],
            'email' => $paramReq_supplier['email']??'',
            'memo' => $paramReq_supplier['memo'],
            'def_paytype' => $paramReq_supplier['def_paytype'],
        ])->id;
        if (isset($paramReq_supplier['paytype'])) {
            foreach ($paramReq_supplier['paytype'] as $key => $val) {
                if (Payment::cheque()->value == $val) {
                    SupplierPayment::createData($id, $val, ['cheque_payable' => $paramReq_supplier['cheque_payable'] ?? null]);
                } else if (Payment::remittance()->value == $val) {
                    SupplierPayment::createData($id, $val, [
                        'bank_cname' => $paramReq_supplier['bank_cname'] ?? null
                        , 'bank_code' => $paramReq_supplier['bank_code'] ?? null
                        , 'bank_acount' => $paramReq_supplier['bank_acount'] ?? null
                        , 'bank_numer' => $paramReq_supplier['bank_numer'] ?? null
                    ]);
                } else {
                    SupplierPayment::createData($id, $val, []);
                }
            }
        }
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
            'chargeman' => 'string',
            'bank_cname' => 'string',
            'bank_code' => 'string',
            'bank_acount' => 'string',
            'bank_numer' => 'string',
            'contact_tel' => 'required|string',
            'contact_address' => 'required|string',
            'contact_person' => 'required|string',
            'email' => 'nullable|email',
            'paytype.*' => 'required|numeric',
            'def_paytype' => 'required|numeric',
        ]);
    }

    //取得欄位資料
    private function getInputValue(Request $request) {
        return $request->only('name', 'nickname', 'vat_no', 'chargeman'
            , 'bank_cname', 'bank_code', 'bank_acount', 'bank_numer', 'cheque_payable'
            , 'contact_tel', 'contact_address', 'contact_person', 'email', 'memo', 'paytype', 'def_paytype');
    }

    public function edit(Request $request,$id)
    {
        $supplierData = Supplier::where('id', '=', $id)->first();
        $payList = SupplierPayment::where('supplier_id', '=', $supplierData->id)->get()->toArray();

        if (!$supplierData) {
            return abort(404);
        }
        $payTypeList = [];
        if (isset($payList)) {
            foreach ($payList as $key => $value) {
                array_push($payTypeList, $value['type']);
            }
        }
        return view('cms.settings.supplier.edit', [
            'id' => $id,
            'supplierData' => $supplierData,
            'payTypeList' => $payTypeList,
            'payList' => $payList,
            'method' => 'edit',
            'formAction' => Route('cms.supplier.edit', ['id' => $id]),
        ]);
    }

    public function update(Request $request, $id)
    {
        $query = $request->query();
        $this->validInputValue($request);
        $paramReq_supplier = $this->getInputValue($request);

        Supplier::where('id', '=', $id)->update([
            'name' => $paramReq_supplier['name'],
            'nickname' => $paramReq_supplier['nickname'],
            'vat_no' => $paramReq_supplier['vat_no'],
            'contact_tel' => $paramReq_supplier['contact_tel'],
            'contact_address' => $paramReq_supplier['contact_address'],
            'contact_person' => $paramReq_supplier['contact_person'],
            'email' => $paramReq_supplier['email']??'',
            'memo' => $paramReq_supplier['memo'],
            'def_paytype' => $paramReq_supplier['def_paytype'],
        ]);

        if (isset($paramReq_supplier['paytype'])) {
            foreach ($paramReq_supplier['paytype'] as $key => $val) {
                if (Payment::cheque()->value == $val) {
                    SupplierPayment::checkToUpdateData($id, $val, ['cheque_payable' => $paramReq_supplier['cheque_payable'] ?? null]);
                } else if (Payment::remittance()->value == $val) {
                    SupplierPayment::checkToUpdateData($id, $val, [
                        'bank_cname' => $paramReq_supplier['bank_cname'] ?? null
                        , 'bank_code' => $paramReq_supplier['bank_code'] ?? null
                        , 'bank_acount' => $paramReq_supplier['bank_acount'] ?? null
                        , 'bank_numer' => $paramReq_supplier['bank_numer'] ?? null
                    ]);
                } else {
                    SupplierPayment::checkToUpdateData($id, $val, []);
                }
            }
            //刪除未勾選方式
            $del_id_arr = array_diff(\App\Enums\Supplier\Payment::getValues(), $paramReq_supplier['paytype']);
            SupplierPayment::where('supplier_id', '=', $id)->whereIn('type', $del_id_arr)->forceDelete();
        }
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
