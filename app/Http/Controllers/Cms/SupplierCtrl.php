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

        $dupParam = Arr::get($query, 'dupParam', '0');
        $dupData = ['0' => null,
                    '1' => 'dupVatNo',
                    '2' => 'dupSupplierName'];

        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;

        $dataList =  Supplier::getSupplierList($title, $dupData[$dupParam])
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
        //判斷統一編號沒有重複
        if ('NIL' != $paramReq_supplier['vat_no']) {
            $supplier = Supplier::where('vat_no', '=', $paramReq_supplier['vat_no'])->get();
            if (isset($supplier) && 0 < count($supplier)) {
                return redirect()->back()->withInput()->withErrors(['vat_no' => '重複的統一編號']);
            }
        }

        $id = Supplier::create([
            'name' => $paramReq_supplier['name'],
            'nickname' => $paramReq_supplier['nickname'],
            'vat_no' => $paramReq_supplier['vat_no'],
            'postal_code' => $paramReq_supplier['postal_code'],
            'contact_address' => $paramReq_supplier['contact_address'],
            'contact_person' => $paramReq_supplier['contact_person'],
            'job' => $paramReq_supplier['job'],
            'contact_tel' => $paramReq_supplier['contact_tel'],
            'extension' => $paramReq_supplier['extension'],
            'fax' => $paramReq_supplier['fax'],
            'mobile_line' => $paramReq_supplier['mobile_line'],
            'email' => $paramReq_supplier['email'],
            'invoice_address' => $paramReq_supplier['invoice_address'],
            'invoice_postal_code' => $paramReq_supplier['invoice_postal_code'],
            'invoice_recipient' => $paramReq_supplier['invoice_recipient'],
            'invoice_email' => $paramReq_supplier['invoice_email'],
            'invoice_phone' => $paramReq_supplier['invoice_phone'],
            'invoice_date' => $paramReq_supplier['invoice_date'],
            'invoice_date_other' => $paramReq_supplier['invoice_date_other'],
            'invoice_ship_fk' => $paramReq_supplier['invoice_ship_fk'],
            'invoice_date_fk' => $paramReq_supplier['invoice_date_fk'],
            'shipping_address' => $paramReq_supplier['shipping_address'],
            'shipping_postal_code' => $paramReq_supplier['shipping_postal_code'],
            'shipping_recipient' => $paramReq_supplier['shipping_recipient'],
            'shipping_phone' => $paramReq_supplier['shipping_phone'],
            'shipping_method_fk' => $paramReq_supplier['shipping_method_fk'],
            'pay_date' => $paramReq_supplier['pay_date'],
            'account_fk' => $paramReq_supplier['account_fk'],
            'account_date' => $paramReq_supplier['account_date'],
            'account_date_other' => $paramReq_supplier['account_date_other'],
            'request_data' => $paramReq_supplier['request_data'],
            'memo' => $paramReq_supplier['memo'],
            'def_paytype' => $paramReq_supplier['def_paytype'],
        ])->id;
        if (isset($paramReq_supplier['paytype'])) {
            foreach ($paramReq_supplier['paytype'] as $key => $val) {
                if (Payment::Cheque()->value == $val) {
                    SupplierPayment::createData($id, $val, ['cheque_payable' => $paramReq_supplier['cheque_payable'] ?? null]);
                } else if (Payment::Remittance()->value == $val) {
                    SupplierPayment::createData($id, $val, [
                        'bank_cname' => $paramReq_supplier['bank_cname'] ?? null
                        , 'bank_code' => $paramReq_supplier['bank_code'] ?? null
                        , 'bank_acount' => $paramReq_supplier['bank_acount'] ?? null
                        , 'bank_numer' => $paramReq_supplier['bank_numer'] ?? null
                    ]);
                } else if (Payment::Other()->value == $val) {
                    SupplierPayment::createData($id, $val, [
                        'other' => $paramReq_supplier['other'] ?? null,
                    ]);
                } else {
                    SupplierPayment::createData($id, $val, []);
                }
            }
        }
        wToast(__('Add finished.'));
        return redirect(Route('cms.supplier.index', [
            'id' => $id,
            'query' => $query
        ]));
    }

    //驗證資料
    private function validInputValue(Request $request) {
        $request->validate([
            'name'                 => 'required|string',
            'nickname'             => 'required|string',
            'vat_no'               => 'required|string',
            'postal_code'          => 'required|string',
            'contact_address'      => 'nullable|string',
            'contact_person'       => 'required|string',
            'job'                  => 'required|string',
            'contact_tel'          => 'required|string',
            'extension'            => 'nullable|string',
            'fax'                  => 'nullable|string',
            'mobile_line'          => 'required|string',
            'email'                => 'nullable|string',
            'invoice_address'      => 'nullable|string',
            'invoice_postal_code'  => 'nullable|string',
            'invoice_recipient'    => 'nullable|string',
            'invoice_email'        => 'nullable|string',
            'invoice_phone'        => 'nullable|string',
            'invoice_date'         => 'nullable|int|min:1|max:31',
            'invoice_date_other'   => 'nullable|string',
            'invoice_ship_fk'      => 'required|string',
            'invoice_date_fk'      => 'required|string',
            'shipping_address'     => 'nullable|string',
            'shipping_postal_code' => 'nullable|string',
            'shipping_recipient'   => 'nullable|string',
            'shipping_phone'       => 'nullable|string',
            'shipping_method_fk'   => 'required|string',
            'pay_date'             => 'nullable|date_format:"Y-m-d"',
            'account_fk'           => 'required|string',
            'account_date'         => 'nullable|int|min:1|max:31',
            'account_date_other'   => 'nullable|string',
            'request_data'         => 'nullable|string',
            'memo'                 => 'nullable|string',
            'def_paytype'          => 'required|string',
            'paytype'          => 'required|array',
            'paytype.*'          => 'required|string',
            'bank_cname'          => 'nullable|string',
            'bank_code'          => 'nullable|string',
            'bank_acount'          => 'nullable|string',
            'bank_numer'          => 'nullable|string',
            'cheque_payable'          => 'nullable|string',
            'other'          => 'nullable|string',
        ]);
    }

    //取得欄位資料
    private function getInputValue(Request $request) {
        return $request->only(
            'name',
            'nickname',
            'vat_no',
            'postal_code',
            'contact_address',
            'contact_person',
            'job',
            'contact_tel',
            'extension',
            'fax',
            'mobile_line',
            'email',
            'invoice_address',
            'invoice_postal_code',
            'invoice_recipient',
            'invoice_email',
            'invoice_phone',
            'invoice_date',
            'invoice_date_other',
            'invoice_ship_fk',
            'invoice_date_fk',
            'shipping_address',
            'shipping_postal_code',
            'shipping_recipient',
            'shipping_phone',
            'shipping_method_fk',
            'pay_date',
            'account_fk',
            'account_date',
            'account_date_other',
            'request_data',
            'memo',
            'def_paytype',
            'paytype',
            'bank_cname',
            'bank_code',
            'bank_acount',
            'bank_numer',
            'cheque_payable',
            'other',
        );
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

        //判斷除了此ID之外的統一編號沒有重複
        if ('NIL' != $paramReq_supplier['vat_no']) {
            $supplier = Supplier::where('vat_no', '=', $paramReq_supplier['vat_no'])
                ->where('id', '<>', $id)
                ->get();
            if (isset($supplier) && 0 < count($supplier)) {
                return redirect()->back()->withInput()->withErrors(['vat_no' => '重複的統一編號']);
            }
        }

        Supplier::where('id', '=', $id)->update([
            'name' => $paramReq_supplier['name'],
            'nickname' => $paramReq_supplier['nickname'],
            'vat_no' => $paramReq_supplier['vat_no'],
            'postal_code' => $paramReq_supplier['postal_code'],
            'contact_address' => $paramReq_supplier['contact_address'],
            'contact_person' => $paramReq_supplier['contact_person'],
            'job' => $paramReq_supplier['job'],
            'contact_tel' => $paramReq_supplier['contact_tel'],
            'extension' => $paramReq_supplier['extension'],
            'fax' => $paramReq_supplier['fax'],
            'mobile_line' => $paramReq_supplier['mobile_line'],
            'email' => $paramReq_supplier['email'],
            'invoice_address' => $paramReq_supplier['invoice_address'],
            'invoice_postal_code' => $paramReq_supplier['invoice_postal_code'],
            'invoice_recipient' => $paramReq_supplier['invoice_recipient'],
            'invoice_email' => $paramReq_supplier['invoice_email'],
            'invoice_phone' => $paramReq_supplier['invoice_phone'],
            'invoice_date' => $paramReq_supplier['invoice_date'],
            'invoice_date_other' => $paramReq_supplier['invoice_date_other'],
            'invoice_ship_fk' => $paramReq_supplier['invoice_ship_fk'],
            'invoice_date_fk' => $paramReq_supplier['invoice_date_fk'],
            'shipping_address' => $paramReq_supplier['shipping_address'],
            'shipping_postal_code' => $paramReq_supplier['shipping_postal_code'],
            'shipping_recipient' => $paramReq_supplier['shipping_recipient'],
            'shipping_phone' => $paramReq_supplier['shipping_phone'],
            'shipping_method_fk' => $paramReq_supplier['shipping_method_fk'],
            'pay_date' => $paramReq_supplier['pay_date'],
            'account_fk' => $paramReq_supplier['account_fk'],
            'account_date' => $paramReq_supplier['account_date'],
            'account_date_other' => $paramReq_supplier['account_date_other'],
            'request_data' => $paramReq_supplier['request_data'],
            'memo' => $paramReq_supplier['memo'],
            'def_paytype' => $paramReq_supplier['def_paytype'],
        ]);

        if (isset($paramReq_supplier['paytype'])) {
            foreach ($paramReq_supplier['paytype'] as $key => $val) {
                if (Payment::Cheque()->value == $val) {
                    SupplierPayment::checkToUpdateData($id, $val, ['cheque_payable' => $paramReq_supplier['cheque_payable'] ?? null]);
                } else if (Payment::Remittance()->value == $val) {
                    SupplierPayment::checkToUpdateData($id, $val, [
                        'bank_cname' => $paramReq_supplier['bank_cname'] ?? null
                        , 'bank_code' => $paramReq_supplier['bank_code'] ?? null
                        , 'bank_acount' => $paramReq_supplier['bank_acount'] ?? null
                        , 'bank_numer' => $paramReq_supplier['bank_numer'] ?? null
                    ]);
                } else if (Payment::Other()->value == $val) {
                    SupplierPayment::checkToUpdateData($id, $val, [
                        'other' => $paramReq_supplier['other'] ?? null,
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
        return redirect(Route('cms.supplier.index', [
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
