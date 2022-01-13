<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Depot;
use App\Models\PayingOrders;
use App\Models\Purchase;
use App\Models\PurchaseInbound;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class PurchaseCtrl extends Controller
{

    public function index(Request $request)
    {
        $query = $request->query();
        $startDate = Arr::get($query, 'startDate', date('Y-m-d'));
        $endDate = Arr::get($query, 'endDate', date('Y-m-d', strtotime(date('Y-m-d') . '+ 1 days')));
        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;

        $purchase_sn = Arr::get($query, 'purchase_sn', '');
        $title = Arr::get($query, 'title', '');
        $sku = Arr::get($query, 'sku', '');
        $purchase_user_id = Arr::get($query, 'purchase_user_id', []);
        $purchase_sdate = Arr::get($query, 'purchase_sdate', '');
        $purchase_edate = Arr::get($query, 'purchase_edate', '');
        $supplier_id = Arr::get($query, 'supplier_id', '');
        $depot_id = Arr::get($query, 'depot_id', '');
        $inbound_user_id = Arr::get($query, 'inbound_user_id', []);
        $inbound_status = Arr::get($query, 'inbound_status', []);
        $inbound_sdate = Arr::get($query, 'inbound_sdate', '');
        $inbound_edate = Arr::get($query, 'inbound_edate', '');
        $expire_day = Arr::get($query, 'expire_day', '');
        $type = Arr::get($query, 'type', '0'); //0:明細 1:總表

        $dataList = null;
        if ('0' === $type) {
            $dataList = PurchaseItem::getPurchaseDetailList(
                $purchase_sn
                , $title
                , $sku
                , $purchase_user_id
                , $purchase_sdate
                , $purchase_edate
                , $supplier_id
                , $depot_id
                , $inbound_user_id
                , $inbound_status
                , $inbound_sdate
                , $inbound_edate
                , $expire_day)
                ->paginate($data_per_page)->appends($query);
        } else {
            $dataList = PurchaseItem::getPurchaseOverviewList(
                $purchase_sn
                , $title
                , $sku
                , $purchase_user_id
                , $purchase_sdate
                , $purchase_edate
                , $supplier_id
                , $depot_id
                , $inbound_user_id
                , $inbound_status
                , $inbound_sdate
                , $inbound_edate
                , $expire_day)
                ->paginate($data_per_page)->appends($query);
        }

        return view('cms.commodity.purchase.list', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'dataList' => $dataList,
            'data_per_page' => $data_per_page
            , 'userList' => User::all()
            , 'depotList' => Depot::all()
            , 'supplierList' => Supplier::getSupplierList()->get()

            , 'purchase_sn' => $purchase_sn
            , 'title' => $title
            , 'sku' => $sku
            , 'purchase_user_id' => $purchase_user_id
            , 'purchase_sdate' => $purchase_sdate
            , 'purchase_edate' => $purchase_edate
            , 'supplier_id' => $supplier_id
            , 'depot_id' => $depot_id
            , 'inbound_user_id' => $inbound_user_id
            , 'inbound_status' => $inbound_status
            , 'inbound_sdate' => $inbound_sdate
            , 'inbound_edate' => $inbound_edate
            , 'expire_day' => $expire_day
        ]);
    }

    public function create(Request $request)
    {
        $supplierList = Supplier::getSupplierList()->get();
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
        $purchaseItemReq = $request->only('product_style_id', 'name', 'sku', 'num', 'price', 'memo');

        $purchaseID = Purchase::createPurchase(
            $purchaseReq['supplier'],
            $request->user()->id,
            $purchaseReq['scheduled_date'],
        );

        $input = [];
        if (isset($purchaseItemReq['product_style_id'])) {
            foreach ($purchaseItemReq['product_style_id'] as $key => $val) {
                array_push($input, [
                    "purchase_id" => $purchaseID,
                    "product_style_id" => $val,
                    "title" => $purchaseItemReq['name'][$key],
                    "sku" => $purchaseItemReq['sku'][$key],
                    "price" => $purchaseItemReq['price'][$key],
                    "num" => $purchaseItemReq['num'][$key],
                    "memo" => $purchaseItemReq['memo'][$key],
                ]);
            }
            PurchaseItem::insert($input);
        }

        // //0:先付(訂金) / 1:先付(一次付清) / 2:貨到付款
        // $deposit_pay_id = null;
        // $final_pay_id = null;
        // if ("0" == $v['pay_type']) {
        //     //訂金、尾款都可填
        //     PayingOrder::createPayingOrder(
        //         $purchaseItemID,
        //         0,
        //         'ABCE',
        //         900,
        //         '2021-12-13 00:00:00',
        //         '第一筆備註 訂金'
        //     );
        // } else if ("1" == $v['pay_type'] || "2" == $v['pay_type']) {
        //     //只有尾款都可填
        // }

        wToast(__('Add finished.'));
        return redirect(Route('cms.purchase.edit', [
            'id' => $purchaseID,
            'query' => $query
        ]));
    }

    //驗證資料
    private function validInputValue(Request $request)
    {
        $request->validate([
            'supplier' => 'required|numeric',
            'scheduled_date' => 'required|string',
            'product_style_id.*' => 'required|numeric',
            'name.*' => 'required|string',
            'sku.*' => 'required|string',
            'price.*' => 'required|numeric',
            'num.*' => 'required|numeric',
        ]);
    }

    public function edit(Request $request, $id)
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

        $supplierList = Supplier::getSupplierList()->get();

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
            'breadcrumb_data' => $purchaseData->purchase_sn,
        ]);
    }

    public function update(Request $request, $id)
    {
        $query = $request->query();
        $this->validInputValue($request);

        $purchaseReq = $request->only('supplier', 'scheduled_date');
        $purchaseItemReq = $request->only('item_id', 'product_style_id', 'name', 'sku', 'num', 'price', 'memo');


        //判斷是否有付款單，有則不可新增刪除商品款式
        $payingOrderList = PayingOrders::getPayingOrdersWithPurchaseID($id)->get();
        if (0 < count($payingOrderList)) {
            if (isset($request['del_item_id']) && null != $request['del_item_id']) {
                throw ValidationException::withMessages(['item_error' => '有付款單，有則不可刪除商品款式']);
            }
            if (isset($purchaseItemReq['item_id'])) {
                foreach ($purchaseItemReq['item_id'] as $key => $val) {
                    $itemId = $purchaseItemReq['item_id'][$key];
                    //有值則做更新
                    //itemId = null 代表新資料
                    if (null == $itemId) {
                        throw ValidationException::withMessages(['item_error' => '有付款單，有則不可新增商品款式']);
                        break;
                    }
                }
            }
        }

        $changeStr = '';
        $changeStr .= $this->checkToUpdatePurchaseData($id, $purchaseReq);

        //刪除現有款式
        if (isset($request['del_item_id']) && null != $request['del_item_id']) {
            $changeStr .= 'delete purchaseItem id:' . $request['del_item_id'];
            $del_item_id_arr = explode(",", $request['del_item_id']);
            PurchaseItem::whereIn('id', $del_item_id_arr)->delete();
        }

        if (isset($purchaseItemReq['item_id'])) {
            $newData = [];
            foreach ($purchaseItemReq['item_id'] as $key => $val) {
                $itemId = $purchaseItemReq['item_id'][$key];
                //有值則做更新
                //itemId = null 代表新資料
                if (null != $itemId) {
                    $changeStr = $this->checkToUpdatePurchaseItemData($itemId, $purchaseItemReq, $key, $changeStr);
                } else {
                    $changeStr .= ' add item:' . $purchaseItemReq['name'][$key];
                    array_push($newData, [
                        "purchase_id" => $id,
                        "product_style_id" => $purchaseItemReq['product_style_id'][$key],
                        "title" => $purchaseItemReq['name'][$key],
                        "sku" => $purchaseItemReq['sku'][$key],
                        "price" => $purchaseItemReq['price'][$key],
                        "num" => $purchaseItemReq['num'][$key],
                        "memo" => $purchaseItemReq['memo'][$key],
                    ]);
                }
            }
            PurchaseItem::insert($newData);
        }

        wToast(__('Edit finished.') . ' ' . $changeStr);
        return redirect(Route('cms.purchase.edit', [
            'id' => $id,
            'query' => $query
        ]));
    }

    public function destroy(Request $request, $id)
    {
        Purchase::where('id', '=', $id)->delete();
        wToast(__('Delete finished.'));
        return redirect(Route('cms.purchase.index'));
    }

    public function checkToUpdatePurchaseData($id, array $purchaseReq)
    {
        $purchase = Purchase::where('id', '=', $id)
            ->select('supplier_id')
            ->selectRaw('DATE_FORMAT(scheduled_date,"%Y-%m-%d") as scheduled_date')
            ->get()->first();
        $purchase->supplier_id = $purchaseReq['supplier'];
        $purchase->scheduled_date = $purchaseReq['scheduled_date'];

        $changeStr = "";
        if ($purchase->isDirty()) {
            foreach ($purchase->getDirty() as $key => $val) {
                $changeStr .= ' ' . $key . ' change to ' . $val;
            }
            Purchase::where('id', $id)->update([
                "supplier_id" => $purchaseReq['supplier'],
                "scheduled_date" => $purchaseReq['scheduled_date'],
            ]);
        }
        return $changeStr;
    }

    public function checkToUpdatePurchaseItemData($itemId, array $purchaseItemReq, $key, string $changeStr)
    {
        $purchaseItem = PurchaseItem::where('id', '=', $itemId)
            ->select('price', 'num')
            ->get()->first();
        $purchaseItem->price = $purchaseItemReq['price'][$key];
        $purchaseItem->num = $purchaseItemReq['num'][$key];
        $purchaseItem->memo = $purchaseItemReq['memo'][$key];
        if ($purchaseItem->isDirty()) {
            foreach ($purchaseItem->getDirty() as $dirtykey => $dirtyval) {
                $changeStr .= ' itemID:' . $itemId . ' ' . $dirtykey . ' change to ' . $dirtyval;
            }
            PurchaseItem::where('id', $itemId)->update([
                "price" => $purchaseItemReq['price'][$key],
                "num" => $purchaseItemReq['num'][$key],
                "memo" => $purchaseItemReq['memo'][$key],
            ]);
        }
        return $changeStr;
    }

    //結案
    public function close(Request $request, $id) {
        Purchase::where('id', $id)->update(['close_date' => date('Y-m-d H:i:s')]);

        wToast(__('Close finished.'));
        return redirect(Route('cms.purchase.inbound', [
            'id' => $id,
        ]));
    }

    public function inbound(Request $request, $id) {
        $purchaseData = Purchase::getPurchase($id)->first();
        $inboundList = PurchaseInbound::getInboundList($id)->get()->toArray();
        $inboundOverviewList = PurchaseInbound::getOverviewInboundList($id)->get()->toArray();

        $depotList = Depot::all()->toArray();
        return view('cms.commodity.purchase.inbound', [
            'purchaseData' => $purchaseData,
            'id' => $id,
            'inboundList' => $inboundList,
            'inboundOverviewList' => $inboundOverviewList,
            'depotList' => $depotList,
            'formAction' => Route('cms.purchase.store_inbound', ['id' => $id,]),
            'formActionClose' => Route('cms.purchase.close', ['id' => $id,]),
            'breadcrumb_data' => $purchaseData->purchase_sn,
        ]);
    }

    public function storeInbound(Request $request, $id)
    {
//        dd($request->all());
        $request->validate([
            'depot_id' => 'required|numeric',
            'product_style_id.*' => 'required|numeric',
            'inbound_date.*' => 'required|string',
            'inbound_num.*' => 'required|numeric|min:1',
            'error_num.*' => 'required|numeric|min:0',
            'status.*' => 'required|numeric|min:0',
            'expiry_date.*' => 'required|string',
        ]);
        $depot_id = $request->input('depot_id');
        $inboundItemReq = $request->only('product_style_id', 'inbound_date', 'inbound_num', 'error_num', 'inbound_memo', 'status', 'expiry_date', 'inbound_memo');

        if (isset($inboundItemReq['product_style_id'])) {
            foreach ($inboundItemReq['product_style_id'] as $key => $val) {
                $purchaseInboundID = PurchaseInbound::createInbound(
                    $id,
                    $inboundItemReq['product_style_id'][$key],
                    $inboundItemReq['expiry_date'][$key],
                    $inboundItemReq['status'][$key],
                    $inboundItemReq['inbound_date'][$key],
                    $inboundItemReq['inbound_num'][$key],
                    $inboundItemReq['error_num'][$key],
                    $depot_id,
                    $request->user()->id,
                    $inboundItemReq['inbound_memo'][$key]
                );
            }
        }
        wToast(__('Add finished.'));
        return redirect(Route('cms.purchase.inbound', [
            'id' => $id,
        ]));
    }

    public function deleteInbound(Request $request, $id)
    {
        $inboundData = PurchaseInbound::where('id', '=', $id);
        $inboundDataGet = $inboundData->get()->first();
        $purchase_id = '';
        if (null != $inboundDataGet) {
            $purchase_id = $inboundDataGet->purchase_id;
            PurchaseInbound::delInbound($id, $request->user()->id);
        }
        wToast(__('Delete finished.'));
        return redirect(Route('cms.purchase.inbound', [
            'id' => $purchase_id,
        ]));
    }
}
