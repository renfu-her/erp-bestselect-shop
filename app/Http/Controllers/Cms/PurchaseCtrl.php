<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\PayingOrders;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PurchaseCtrl extends Controller
{

    public function index(Request $request)
    {
        //
        $query = $request->query();
        $startDate = Arr::get($query, 'startDate', date('Y-m-d'));
        $endDate = Arr::get($query, 'endDate', date('Y-m-d', strtotime(date('Y-m-d') . '+ 1 days')));
        $title = Arr::get($query, 'title', '');
        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;

        $dataList =  Purchase::getPurchaseList($startDate, $endDate, true, $title)
            ->paginate($data_per_page)->appends($query);

        return view('cms.settings.purchase.list', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'dataList' => $dataList,
            'title' => $title,
            'data_per_page' => $data_per_page,
        ]);
    }

    public function create(Request $request)
    {
        $supplierList =  Supplier::getSupplierList()->get();
        return view('cms.settings.purchase.edit', [
            'method' => 'create',
            'supplierList' => $supplierList,
            'formAction' => Route('cms.purchase.create'),
        ]);
    }

    public function store(Request $request)
    {
//        $query = $request->query();
//        $this->validInputValue($request);
//        $v = $this->getInputValue($request);
//
//        //0:先付(訂金) / 1:先付(一次付清) / 2:貨到付款
//        $deposit_pay_id = null;
//        $final_pay_id = null;
//        if ("0" == $v['pay_type']) {
//            //訂金、尾款都可填
//        } else if ("1" == $v['pay_type'] || "2" == $v['pay_type']) {
//            //只有尾款都可填
//        }
//
//        $id = Purchase::create([
//            'supplier' => $v['supplier'],
//            'purchase_id' => 1,
//            'bank_cname' => $v['bank_cname'],
//            'bank_code' => $v['bank_code'],
//            'bank_acount' => $v['bank_acount'],
//            'bank_numer' => $v['bank_numer'],
//            'pay_type' => $v['pay_type'],
//            'logistic_price' => $v['logistic_price'],
//            'scheduled_date' => $v['scheduled_date'],
//        ]);
//        wToast(__('Add finished.'));
//        return redirect(Route('cms.supplier.edit', [
//            'id' => $id,
//            'query' => $query
//        ]));
    }

    //驗證資料
    private function validInputValue(Request $request) {
        $request->validate([
            'supplier' => 'required|numeric',
            'scheduled_date' => 'required|string',
            'bank_cname' => 'required|string',
            'bank_code' => 'required|string',
            'bank_acount' => 'required|string',
            'bank_numer' => 'required|string',
            'pay_type' => 'required|string',
        ]);
    }

    //取得欄位資料
    private function getInputValue(Request $request) {
        return $request->only('supplier', 'scheduled_date', 'deposit_pay_num', 'final_pay_num'
            , 'bank_cname', 'bank_code', 'bank_acount', 'bank_numer'
            , 'pay_type', 'deposit_pay_price', 'deposit_pay_date', 'final_pay_date', 'logistic_price');
    }

    public function edit(Request $request,$id)
    {
        $purchaseData = Purchase::getPurchase($id)->first();
        if (!$purchaseData) {
            return abort(404);
        }
        $payingOrderList = PayingOrders::getPayingOrdersWithPurchaseID($id)->get();

        $depositPayData = null;
        $finalPayData = null;
        if (0 < count($payingOrderList)) {
            foreach ($payingOrderList as $payingOrderItem) {
                if ($payingOrderItem->type == 0) {
                    $depositPayData = $payingOrderItem;
                } else if ($payingOrderItem->type == 1) {
                    $finalPayData = $payingOrderItem;
                }
            }
        }

        $supplierList =  Supplier::getSupplierList()->get();
        return view('cms.settings.purchase.edit', [
            'id' => $id,
            'data' => $purchaseData,
            'payingOrderData' => $payingOrderList,
            'depositPayData' => $depositPayData,
            'finalPayData' => $finalPayData,
            'method' => 'edit',
            'supplierList' => $supplierList,
            'formAction' => Route('cms.purchase.edit', ['id' => $id]),
        ]);
    }

//    public function update(Request $request, $id)
//    {
//        $query = $request->query();
//        $this->validInputValue($request);
//        $v = $this->getInputValue($request);
//
//        Purchase::where('id', '=', $id)->update($v);
//        wToast(__('Edit finished.'));
//        return redirect(Route('cms.purchase.edit', [
//            'id' => $id,
//            'query' => $query
//        ]));
//    }
//
    public function destroy(Request $request, $id)
    {
        Purchase::where('id', '=', $id)->delete();
        wToast(__('Delete finished.'));
        return redirect(Route('cms.purchase.index'));
    }
}
