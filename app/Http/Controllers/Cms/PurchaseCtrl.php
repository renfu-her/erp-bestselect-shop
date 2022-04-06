<?php

namespace App\Http\Controllers\Cms;

use App\Enums\Delivery\Event;
use App\Enums\Purchase\LogEvent;
use App\Http\Controllers\Controller;
use App\Models\AllGrade;
use App\Models\Depot;
use App\Models\PayingOrder;
use App\Models\Purchase;
use App\Models\PurchaseInbound;
use App\Models\PurchaseItem;
use App\Models\PurchaseLog;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Enums\Purchase\InboundStatus;

class PurchaseCtrl extends Controller
{

    public function index(Request $request)
    {
        $query = $request->query();
        $startDate = Arr::get($query, 'startDate', date('Y-m-d'));
        $endDate = Arr::get($query, 'endDate', date('Y-m-d', strtotime(date('Y-m-d') . '+ 1 days')));
        $data_per_page = Arr::get($query, 'data_per_page', 50);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 50;

        $all_inbound_status = [];
        foreach (InboundStatus::asArray() as $data) {
            $all_inbound_status[$data] = InboundStatus::getDescription($data);
        }

        $purchase_sn = Arr::get($query, 'purchase_sn', '');
        $title = Arr::get($query, 'title', '');
//        $sku = Arr::get($query, 'sku', '');
        $purchase_user_id = Arr::get($query, 'purchase_user_id', []);
        $purchase_sdate = Arr::get($query, 'purchase_sdate', '');
        $purchase_edate = Arr::get($query, 'purchase_edate', '');
        $supplier_id = Arr::get($query, 'supplier_id', '');
        $depot_id = Arr::get($query, 'depot_id', '');
        $inbound_user_id = Arr::get($query, 'inbound_user_id', []);
        $inbound_status = Arr::get($query, 'inbound_status', implode(',', array_keys($all_inbound_status)));
        $inbound_sdate = Arr::get($query, 'inbound_sdate', '');
        $inbound_edate = Arr::get($query, 'inbound_edate', '');
        $expire_day = Arr::get($query, 'expire_day', '');
        $type = Arr::get($query, 'type', '0'); //0:明細 1:總表

        $inbound_status_arr = [];
        if ('' != $inbound_status) {
            $inbound_status_arr = explode(',', $inbound_status);
        }

        $dataList = null;
        if ('0' === $type) {
            $dataList = PurchaseItem::getPurchaseDetailList(
                $purchase_sn
                , $title
                , $purchase_user_id
                , $purchase_sdate
                , $purchase_edate
                , $supplier_id
                , $depot_id
                , $inbound_user_id
                , $inbound_status_arr
                , $inbound_sdate
                , $inbound_edate
                , $expire_day)
                ->paginate($data_per_page)->appends($query);
        } else {
            $dataList = PurchaseItem::getPurchaseOverviewList(
                $purchase_sn
                , $title
                , $purchase_user_id
                , $purchase_sdate
                , $purchase_edate
                , $supplier_id
                , $depot_id
                , $inbound_user_id
                , $inbound_status_arr
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
            , 'purchase_user_id' => $purchase_user_id
            , 'purchase_sdate' => $purchase_sdate
            , 'purchase_edate' => $purchase_edate
            , 'supplier_id' => $supplier_id
            , 'depot_id' => $depot_id
            , 'inbound_user_id' => $inbound_user_id
            , 'inbound_status' => $inbound_status
            , 'all_inbound_status' => $all_inbound_status
            , 'inbound_sdate' => $inbound_sdate
            , 'inbound_edate' => $inbound_edate
            , 'expire_day' => $expire_day
            , 'type' => $type
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

        $purchaseReq = $request->only('supplier', 'scheduled_date', 'supplier_sn');
        $purchaseItemReq = $request->only('product_style_id', 'name', 'sku', 'num', 'price', 'memo');
        $purchasePayReq = $request->only('logistics_price', 'logistics_memo', 'invoice_num', 'invoice_date');

        $supplier = Supplier::where('id', '=', $purchaseReq['supplier'])->get()->first();
        $rePcs = Purchase::createPurchase(
            $purchaseReq['supplier'],
            $supplier->name,
            $supplier->nickname,
            $purchaseReq['supplier_sn'] ?? null,
            $request->user()->id,
            $request->user()->name,
            $purchaseReq['scheduled_date'],
            $purchasePayReq['logistics_price'] ?? null,
            $purchasePayReq['logistics_memo'] ?? null,
            $purchasePayReq['invoice_num'] ?? null,
            $purchasePayReq['invoice_date'] ?? null,
        );
        $purchaseID = null;
        if (isset($rePcs['id'])) {
            $purchaseID = $rePcs['id'];
        }

        $result = null;
        $result = DB::transaction(function () use ($purchaseItemReq, $rePcs, $request, $purchaseID
        ) {
            if (isset($purchaseItemReq['product_style_id']) && isset($purchaseID)) {
                foreach ($purchaseItemReq['product_style_id'] as $key => $val) {
                    $rePcsICP = PurchaseItem::createPurchase(
                        [
                            'purchase_id' => $purchaseID,
                            'product_style_id' => $val,
                            'title' => $purchaseItemReq['name'][$key],
                            'sku' => $purchaseItemReq['sku'][$key],
                            'price' => $purchaseItemReq['price'][$key],
                            'num' => $purchaseItemReq['num'][$key],
                            'temp_id' => $purchaseItemReq['temp_id'][$key] ?? null,
                            'memo' => $purchaseItemReq['memo'][$key],
                        ],
                        $request->user()->id, $request->user()->name
                    );
                    if ($rePcsICP['success'] == 0) {
                        DB::rollBack();
                        return $rePcsICP;
                    }
                }
            }
            return ['success' => 1, 'error_msg' => ""];
        });
        if ($result['success'] == 0) {
            wToast($result['error_msg']);
        } else {
            wToast(__('Add finished.'));
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

        return redirect(Route('cms.purchase.edit', [
            'id' => $rePcs['id'],
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
//        $purchaseItemData = PurchaseItem::getData($id)->get()->toArray();
        $purchaseItemData = PurchaseItem::getDataWithInbound($id)->get()->toArray();

        if (!$purchaseData) {
            return abort(404);
        }

        //做一陣列 整理各款式商品的入庫人員，將重複的去除
        $inbound_name_arr = [];
        $inbound_names = '';
        if (null != $purchaseItemData && 0 < count($purchaseItemData)) {
            foreach ($purchaseItemData as $item) {
                if (isset($item->inbound_user_name)) {
                    $item_name_arr = explode(',', $item->inbound_user_name);
                    foreach ($item_name_arr as $item_name) {
                        array_push($inbound_name_arr, $item_name);
                    }
                }
            }
            $inbound_name_arr = array_unique($inbound_name_arr);
            $inbound_names = implode(',', $inbound_name_arr);
        }

        $hasCreatedDepositPayment = false;  // 是否已有訂金單
        $hasCreatedFinalPayment = false;  // 是否已有尾款單
        //TODO Design Enum Type 訂金、尾款單（建立與否、付款與否)
        $hasReceivedDepositPayment = false;
        $hasReceivedFinalPayment = false;
        $payingOrderList = PayingOrder::getPayingOrdersWithPurchaseID($id)->get();

        $depositPayData = null;
        $finalPayData = null;
        if (0 < count($payingOrderList)) {
            foreach ($payingOrderList as $payingOrderItem) {
                $payingOrderId = $payingOrderItem->id;
                if ($payingOrderItem->type === 0) {
                    $hasCreatedDepositPayment = true;
                    $depositPayData = $payingOrderItem;
                    if (PayingOrder::find($payingOrderId)->accountPayable) {
                        $hasReceivedDepositPayment = true;
                    }
                } elseif ($payingOrderItem->type === 1) {
                    $hasCreatedFinalPayment = true;
                    $finalPayData = $payingOrderItem;
                    if (PayingOrder::find($payingOrderId)->accountPayable) {
                        $hasReceivedFinalPayment = true;
                    }
                }
            }
        }

        $supplierList = Supplier::getSupplierList()->get();

        return view('cms.commodity.purchase.edit', [
            'id' => $id,
            'purchaseData' => $purchaseData,
            'purchaseItemData' => $purchaseItemData,
            // 'payingOrderData' => $payingOrderList,
            'hasCreatedDepositPayment'  => $hasCreatedDepositPayment,
            'hasCreatedFinalPayment'    => $hasCreatedFinalPayment,
            'hasReceivedDepositPayment' => $hasReceivedDepositPayment,
            'hasReceivedFinalPayment'   => $hasReceivedFinalPayment,
            'depositPayData'            => $depositPayData,
            'finalPayData'              => $finalPayData,
            'method' => 'edit',
            'supplierList' => $supplierList,
            'formAction' => Route('cms.purchase.edit', ['id' => $id]),
            'breadcrumb_data' => ['id' => $id, 'sn' => $purchaseData->purchase_sn],

            'inbound_names' => $inbound_names,
        ]);
    }

    public function update(Request $request, $id)
    {
        $query = $request->query();
        $this->validInputValue($request);

        $taxReq = $request->input('tax');
        $purchaseReq = $request->only('supplier', 'scheduled_date', 'supplier_sn');
        $purchaseItemReq = $request->only('item_id', 'product_style_id', 'name', 'sku', 'num', 'price', 'memo');
        $purchasePayReq = $request->only('tax', 'logistics_price', 'logistics_memo', 'invoice_num', 'invoice_date');


        //判斷是否有付款單，有則不可新增刪除商品款式
        $payingOrderList = PayingOrder::getPayingOrdersWithPurchaseID($id)->get();
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
        $repcsCTPD = Purchase::checkToUpdatePurchaseData($id, $purchaseReq, $changeStr, $request->user()->id, $request->user()->name, $taxReq, $purchasePayReq);
        $changeStr .= $repcsCTPD['error_msg'];

        $purchaseGet = Purchase::where('id', '=', $id)->get()->first();
        //刪除現有款式
        if (isset($request['del_item_id']) && null != $request['del_item_id']) {
            $changeStr .= 'delete purchaseItem id:' . $request['del_item_id'];
            $del_item_id_arr = explode(",", $request['del_item_id']);
            $rePcsDI = PurchaseItem::deleteItems($purchaseGet->id, $del_item_id_arr, $request->user()->id, $request->user()->name);
            if ($rePcsDI['success'] == 0) {
                $changeStr .= $rePcsDI['error_msg'];
            }
        }

        if (isset($purchaseItemReq['item_id'])) {
            foreach ($purchaseItemReq['item_id'] as $key => $val) {
                $itemId = $purchaseItemReq['item_id'][$key];
                //有值則做更新
                //itemId = null 代表新資料
                if (null != $itemId) {
                    $result = PurchaseItem::checkToUpdatePurchaseItemData($itemId, $purchaseItemReq, $key, $changeStr, $request->user()->id, $request->user()->name, $purchasePayReq);
                    $changeStr = $result['error_msg'];
                } else {
                    $changeStr .= ' add item:' . $purchaseItemReq['name'][$key];

                    PurchaseItem::createPurchase(
                        [
                            'purchase_id' => $id,
                            'product_style_id' => $purchaseItemReq['product_style_id'][$key],
                            'title' => $purchaseItemReq['name'][$key],
                            'sku' => $purchaseItemReq['sku'][$key],
                            'price' => $purchaseItemReq['price'][$key],
                            'num' => $purchaseItemReq['num'][$key],
                            'temp_id' => $purchaseItemReq['temp_id'][$key] ?? null,
                            'memo' => $purchaseItemReq['memo'][$key],
                        ],
                        $request->user()->id, $request->user()->name
                    );
                }
            }
        }
        $changeStr = '';
        wToast(__('Edit finished.') . ' ' . $changeStr);
        return redirect(Route('cms.purchase.edit', [
            'id' => $id,
            'query' => $query
        ]));
    }

    public function destroy(Request $request, $id)
    {
        //判斷若有入庫、付款單 則不可刪除
        $returnMsg = [];
        $inbounds = PurchaseInbound::purchaseInboundList($id)->get()->toArray();
        $payingOrderList = PayingOrder::getPayingOrdersWithPurchaseID($id)->get();
        if (null != $inbounds && 0 < count($inbounds)) {
            $returnMsg = '已入庫無法刪除';
        } else if (null != $payingOrderList && 0 < count($payingOrderList)) {
            $returnMsg = '已有付款單無法刪除';
        } else {
            $result = Purchase::del($id, $request->user()->id, $request->user()->name);
            if ($result['success'] == 0) {
                wToast($result['error_msg']);
            } else {
                $returnMsg = __('Delete finished.');
            }
        }

        wToast($returnMsg);
        return redirect(Route('cms.purchase.index'));
    }

    //結案
    public function close(Request $request, $id) {
        $inboundOverviewList = PurchaseInbound::getOverviewInboundList(Event::purchase()->key, $id)->get()->toArray();
        $errmsg = '';
        if (0 < $inboundOverviewList) {
            foreach ($inboundOverviewList as $key => $data) {
                if (0 < $data->should_enter_num) {
                    $errmsg = '請檢察是否有款式尚未入庫';
                    break;
                }
            }
        } else {
            $errmsg = '未加入商品款式';
        }
        if ('' != $errmsg) {
            throw ValidationException::withMessages(['close_error' => $errmsg]);
        } else {
            Purchase::close($id, $request->user()->id, $request->user()->name);
        }

        wToast(__('Close finished.'));
        return redirect(Route('cms.purchase.inbound', [
            'id' => $id,
        ]));
    }

    public function inbound(Request $request, $id) {
        $purchaseData = Purchase::getPurchase($id)->first();
        $purchaseItemList = PurchaseItem::getDataForInbound($id)->get()->toArray();
        $inboundList = PurchaseInbound::getInboundList(['event' => Event::purchase()->key, 'purchase_id' => $id])->get()->toArray();
        $inboundOverviewList = PurchaseInbound::getOverviewInboundList(Event::purchase()->key, $id)->get()->toArray();

        $depotList = Depot::all()->toArray();
        return view('cms.commodity.purchase.inbound', [
            'purchaseData' => $purchaseData,
            'id' => $id,
            'purchaseItemList' => $purchaseItemList,
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
        $request->validate([
            'depot_id' => 'required|numeric',
            'purchase_item_id.*' => 'required|numeric',
            'product_style_id.*' => 'required|numeric',
            'inbound_date.*' => 'required|string',
            'inbound_num.*' => 'required|numeric|min:1',
            'error_num.*' => 'required|numeric|min:0',
            'status.*' => 'required|numeric|min:0',
            'expiry_date.*' => 'required|string',
        ]);
        $depot_id = $request->input('depot_id');
        $inboundItemReq = $request->only('purchase_item_id', 'product_style_id', 'inbound_date', 'inbound_num', 'error_num', 'inbound_memo', 'status', 'expiry_date', 'inbound_memo');

        if (isset($inboundItemReq['product_style_id'])) {
            $depot = Depot::where('id', '=', $depot_id)->get()->first();

            $result = DB::transaction(function () use ($inboundItemReq, $id, $depot_id, $depot, $request
            ) {
                foreach ($inboundItemReq['product_style_id'] as $key => $val) {

                    $re = PurchaseInbound::createInbound(
                        Event::purchase()->key,
                        $id,
                        $inboundItemReq['purchase_item_id'][$key],
                        $inboundItemReq['product_style_id'][$key],
                        $inboundItemReq['expiry_date'][$key],
                        $inboundItemReq['inbound_date'][$key],
                        $inboundItemReq['inbound_num'][$key],
                        $depot_id,
                        $depot->name,
                        $request->user()->id,
                        $request->user()->name,
                        $inboundItemReq['inbound_memo'][$key]
                    );
                    if ($re['success'] == 0) {
                        DB::rollBack();
                        return $re;
                    }
                }
                return ['success' => 1, 'error_msg' => ""];
            });
            if ($result['success'] == 0) {
                wToast($result['error_msg']);
            } else {
                wToast(__('Add finished.'));
            }
        }
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
            $purchase_id = $inboundDataGet->event_id;
            if (0 < $inboundDataGet->sale_num) {
                wToast('已有售出紀錄 無法刪除');
            } else if (0 < $inboundDataGet->csn_num) {
                wToast('已有寄倉紀錄 無法刪除');
            } else if (0 < $inboundDataGet->consume_num) {
                wToast('已有耗材紀錄 無法刪除');
            } else {
                $re = PurchaseInbound::delInbound($id, $request->user()->id);
                if ($re['success'] == 0) {
                    wToast($re['error_msg']);
                } else {
                    wToast(__('Delete finished.'));
                }
            }
        }
        return redirect(Route('cms.purchase.inbound', [
            'id' => $purchase_id,
        ]));
    }

    /**
     * @param  Request  $request
     * @param  int  $id purchase_id 採購單ID
     * 處理付款單訊息、顯示付款單
     * @return void
     */
    public function payOrder(Request $request, int $id)
    {
        $val = Validator::make($request->all(), [
            'type'    => ['required', 'string', 'regex:/^(0|1)$/'],
            'summary' => ['required', 'string'],
            'price' => ['required', 'int', 'min:1'],
            'memo' => ['nullable', 'string']
        ]);

        $validatedReq = $val->validated();

        //產生付款單
        if ($request->isMethod('POST')) {
            if ($validatedReq['type'] === '1') {
                $totalPrice = self::getPaymentPrice($id)['finalPaymentPrice'];
            } elseif (isset($validatedReq['price'])) {
                $totalPrice = intval($validatedReq['price']);
            }
            $productDefault = DB::table('acc_grade_default')->where('name', '=', 'product')->get()->first();
            $logisticsDefault = DB::table('acc_grade_default')->where('name', '=', 'logistics')->get()->first();
            $prdDefault = json_decode(json_encode($productDefault), true);
            $lgsDefault = json_decode(json_encode($logisticsDefault), true);

            PayingOrder::createPayingOrder(
                $id,
                $request->user()->id,
                $validatedReq['type'],
                $prdDefault['default_grade_id'],
                $lgsDefault['default_grade_id'],
                $totalPrice ?? 0,
                null,
                $request['deposit_summary'] ?? '',
                $request['deposit_memo'] ?? '',
            );
        }

        $paymentPrice = self::getPaymentPrice($id);
        if ($paymentPrice['depositPaymentPrice'] > 0) {
            $depositPaymentData = PayingOrder::getPayingOrdersWithPurchaseID($id, 0)->get()->first();
        } else {
            $depositPaymentData = null;
        }

        $payingOrderData = PayingOrder::getPayingOrdersWithPurchaseID($id, $validatedReq['type'])->get()->first();
        $payingOrderQuery = PayingOrder::find($payingOrderData->id);
        $productGradeName = AllGrade::find($payingOrderQuery->product_grade_id)->eachGrade->name;
        $logisticsGradeName = AllGrade::find($payingOrderQuery->logistics_grade_id)->eachGrade->name;

        $purchaseItemData = PurchaseItem::getPurchaseItemsByPurchaseId($id);

        $purchaseData = Purchase::getPurchase($id)->first();
        $supplier = Supplier::where('id', '=', $purchaseData->supplier_id)->get()->first();

        $purchaseChargemanList = PurchaseItem::getPurchaseChargemanList($id)->get()->unique('user_id');
        $chargemanListArray = [];
        foreach ($purchaseChargemanList as $chargemanList) {
            $chargemanListArray[] = $chargemanList->user_name;
        }
        $chargemen = implode(',', $chargemanListArray);

        $undertaker = DB::table('usr_users')
                        ->where('id', '=', $payingOrderData->usr_users_id)
                        ->get()
                        ->first()
                        ->name;

        //採購單申請公司（系統先預設「喜鴻國際」）
        $appliedCompanyData = DB::table('acc_company')
                            ->where('id', '=', 1)
                            ->get()
                            ->first();
        $accountPayable = PayingOrder::find($payingOrderData->id)->accountPayable;

        if ($accountPayable) {
            $accountant = DB::table('usr_users')
                            ->find($accountPayable->accountant_id_fk, ['name'])
                            ->name;
        }

        return view('cms.commodity.purchase.pay_order', [
            'id' => $id,
            'accountant' => $accountant ?? '',
            'accountPayableId' => $accountPayable->id ?? null,
            'payOrdId' => $payingOrderData->id,
            'type' => ($validatedReq['type'] === '0') ? 'deposit' : 'final',
            'breadcrumb_data' => ['id' => $id, 'sn' => $purchaseData->purchase_sn],
            'formAction' => Route('cms.purchase.index', ['id' => $id,]),
            'supplierUrl' => Route('cms.supplier.edit', ['id' => $supplier->id,]),
            'purchaseData' => $purchaseData,
            'hasReceivedPayment' => !is_null($accountPayable),
            'payingOrderData' => $payingOrderData,
            'productGradeName' => $productGradeName,
            'logisticsGradeName' => $logisticsGradeName,
            'depositPaymentData' => $depositPaymentData,
            'finalPaymentPrice' => $paymentPrice['finalPaymentPrice'],
            'logisticsPrice' => $paymentPrice['logisticsPrice'],
            'purchaseItemData' => $purchaseItemData,
            'chargemen' => $chargemen,
            'undertaker' => $undertaker,
            'appliedCompanyData' => $appliedCompanyData,
            'supplier' => $supplier,
        ]);
    }

    /**
     * 新增訂金付款單
     */
    public function payDeposit(Request $request, $id) {
        $purchaseData = Purchase::getPurchase($id)->first();
//        $supplier = Supplier::where('id', '=', $purchaseData->supplier_id)->get()->first();
//        $purchaseChargemanList = PurchaseItem::getPurchaseChargemanList($id)->get();

//        $payList = SupplierPayment::where('supplier_id', '=', $purchaseData->supplier_id)->get()->toArray();
        $payTypeList = [];
        if (isset($payList)) {
            foreach ($payList as $key => $value) {
                array_push($payTypeList, $value['type']);
            }
        }
//        dd($supplier);

        return view('cms.commodity.purchase.receipt', [
            'type' => 'deposit',
            'id' => $id,
            'purchaseData' => $purchaseData,
//            'supplier' => $supplier,
            'payTypeList' => $payTypeList,
//            'payList' => $payList,
//            'purchaseChargemanList' => $purchaseChargemanList,
            'breadcrumb_data' => ['id' => $id, 'sn' => $purchaseData->purchase_sn],
            'formAction' => Route('cms.purchase.pay-order', ['id' => $id,]),
        ]);
    }

    /**
     * 新增尾款付款單
     */
    public function payFinal(Request $request, $id) {
        $purchaseData = Purchase::getPurchase($id)->first();
        $supplierList = Supplier::getSupplierList()->get();

        $paymentPrice = self::getPaymentPrice($id);
        return view('cms.commodity.purchase.receipt', [
            'type' => 'final',
            'id' => $id,
            'method' => 'create',
            'purchaseData' => $purchaseData,
            'supplierList' => $supplierList,
            'depositPaymentPrice' => $paymentPrice['depositPaymentPrice'],
            'finalPaymentPrice' => $paymentPrice['finalPaymentPrice'],
            'totalPrice' => $paymentPrice['totalPrice'],
            'breadcrumb_data' => ['id' => $id, 'sn' => $purchaseData->purchase_sn],
            'formAction' => Route('cms.purchase.pay-order', ['id' => $id,]),
        ]);
    }
    /**
     * @param  int  $purchaseId
     * 計算付款單的金額，並回傳
     * @return array 回傳付款單（訂金、尾款、運費）的金額 array index:depositPaymentPrice, finalPaymentPrice, logisticsPrice, totalPrice
     */
    public function getPaymentPrice(int $purchaseId)
    {
        $depositPaymentPrice = 0;
        $finalPaymentPrice = 0;
        $totalPrice = 0;

        $purchaseItemData = PurchaseItem::getPurchaseItemsByPurchaseId($purchaseId);
        foreach ($purchaseItemData as $purchaseItem) {
            $totalPrice += $purchaseItem->total_price;
        }
        $logisticsPrice = DB::table('pcs_purchase')
                            ->find($purchaseId, 'logistics_price')
                            ->logistics_price;
        $totalPrice += $logisticsPrice;

        $depositPaymentOrder = PayingOrder::getPayingOrdersWithPurchaseID($purchaseId, 0)->get()->first();
        if ($depositPaymentOrder) {
            $depositPaymentPrice = $depositPaymentOrder->price;
            $finalPaymentPrice = $totalPrice - $depositPaymentPrice;
        } else {
            $depositPaymentPrice = 0;
            $finalPaymentPrice = $totalPrice;
        }

        return [
            'depositPaymentPrice' => $depositPaymentPrice,
            'finalPaymentPrice'   => $finalPaymentPrice,
            'logisticsPrice'      => $logisticsPrice,
            'totalPrice'          => $totalPrice,
        ];
    }

    /**
     * 變更歷史
     */
    public function historyLog(Request $request, $id) {
        $purchaseData = Purchase::getPurchase($id)->first();
        $purchaseLog = PurchaseLog::getData(LogEvent::purchase()->value, $id)->get();
        if (!$purchaseData) {
            return abort(404);
        }

        return view('cms.commodity.purchase.log', [
            'id' => $id,
            'purchaseData' => $purchaseData,
            'purchaseLog' => $purchaseLog,
            'breadcrumb_data' => $purchaseData->purchase_sn,
        ]);
    }
}

