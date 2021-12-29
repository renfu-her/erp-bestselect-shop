<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\PayingOrder;
use App\Models\PayingOrders;
use App\Models\Purchase;
use App\Models\PurchaseItem;
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

        return view('cms.commodity.purchase.list', [
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
        return view('cms.commodity.purchase.edit', [
            'method' => 'create',
            'supplierList' => $supplierList,
            'formAction' => Route('cms.purchase.create'),
        ]);
    }

    public function store(Request $request)
    {
        $query = $request->query();
        $this->validInputValue($request);

        $purchaseReq = $request->only('supplier', 'scheduled_date');
        $purchaseItemReq = $request->only('product_style_id', 'name', 'sku', 'num', 'price');

        $purchaseID = Purchase::createPurchase(
            $purchaseReq['supplier'],
            $request->user()->id,
            $purchaseReq['scheduled_date'],
//            '',
//            $v['pay_type'],
//            null,
//            null,
        );

        $input = [];
        if (isset($purchaseItemReq['product_style_id'])) {
            foreach($purchaseItemReq['product_style_id'] as $key => $val)
            {
                array_push($input, [
                    "purchase_id" => $purchaseID,
                    "product_style_id" => $val,
                    "title" =>  $purchaseItemReq['name'][$key],
                    "sku" =>  $purchaseItemReq['sku'][$key],
                    "price" =>  $purchaseItemReq['price'][$key],
                    "num" =>  $purchaseItemReq['num'][$key],
                ]);
            }
            PurchaseItem::insert($input);
        }

//        //0:先付(訂金) / 1:先付(一次付清) / 2:貨到付款
//        $deposit_pay_id = null;
//        $final_pay_id = null;
//        if ("0" == $v['pay_type']) {
//            //訂金、尾款都可填
////            PayingOrder::createPayingOrder(
////                $purchaseItemID,
////                0,
////                'ABCE',
////                900,
////                '2021-12-13 00:00:00',
////                '第一筆備註 訂金'
////            );
//        } else if ("1" == $v['pay_type'] || "2" == $v['pay_type']) {
//            //只有尾款都可填
//        }

        wToast(__('Add finished.'));
        return redirect(Route('cms.purchase.edit', [
            'id' => $purchaseID,
            'query' => $query
        ]));
    }

    //驗證資料
    private function validInputValue(Request $request) {
        $request->validate([
            'supplier' => 'required|numeric',
            'scheduled_date' => 'required|string',
//            'bank_cname' => 'required|string',
//            'bank_code' => 'required|string',
//            'bank_acount' => 'required|string',
//            'bank_numer' => 'required|string',
//            'pay_type' => 'required|string',
        ]);
    }

    //取得欄位資料
    private function getInputValue(Request $request) {
        return $request->only('supplier', 'scheduled_date'
            , 'product_style_id', 'name', 'num', 'price'
//            , 'deposit_pay_num', 'final_pay_num'
//            , 'bank_cname', 'bank_code', 'bank_acount', 'bank_numer'
//            , 'pay_type', 'deposit_pay_price', 'deposit_pay_date', 'final_pay_price', 'final_pay_date', 'logistic_price'
        );
    }

    public function edit(Request $request,$id)
    {
        $purchaseData = Purchase::getPurchase($id)->first();
        $purchaseItemData = PurchaseItem::getData($id)->get()->toArray();
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
        return view('cms.commodity.purchase.edit', [
            'id' => $id,
            'purchaseData' => $purchaseData,
            'purchaseItemData' => $purchaseItemData,
            'payingOrderData' => $payingOrderList,
            'depositPayData' => $depositPayData,
            'finalPayData' => $finalPayData,
            'method' => 'edit',
            'supplierList' => $supplierList,
            'formAction' => Route('cms.purchase.edit', ['id' => $id]),
        ]);
    }

    public function update(Request $request, $id)
    {
        $query = $request->query();
        dd($request->all());
//        $this->validInputValue($request);
//        $v = $this->getInputValue($request);
//
//        Purchase::where('id', '=', $id)->update($v);
//        wToast(__('Edit finished.'));
//        return redirect(Route('cms.purchase.edit', [
//            'id' => $id,
//            'query' => $query
//        ]));
    }

    public function destroy(Request $request, $id)
    {
        Purchase::where('id', '=', $id)->delete();
        wToast(__('Delete finished.'));
        return redirect(Route('cms.purchase.index'));
    }
}
