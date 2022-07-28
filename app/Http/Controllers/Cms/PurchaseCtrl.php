<?php

namespace App\Http\Controllers\Cms;

use App\Enums\Consignment\AuditStatus;
use App\Enums\Delivery\Event;
use App\Enums\Supplier\Payment;
use App\Http\Controllers\Controller;

use App\Models\AllGrade;
use App\Models\Depot;
use App\Models\PayingOrder;
use App\Models\Purchase;
use App\Models\PayableAccount;
use App\Models\PayableCash;
use App\Models\PayableCheque;
use App\Models\PayableRemit;
use App\Models\PayableForeignCurrency;
use App\Models\PayableOther;
use App\Models\PurchaseInbound;
use App\Models\PurchaseItem;
use App\Models\PurchaseLog;
use App\Models\Supplier;
use App\Models\User;
use App\Models\GeneralLedger;
use App\Models\PayableDefault;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Enums\Purchase\InboundStatus;

use App\Models\AccountPayable;

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
        $audit_status = Arr::get($query, 'audit_status', null);

        $inbound_status_arr = [];
        if ('' != $inbound_status) {
            $inbound_status_arr = explode(',', $inbound_status);
        }

        $dataList = null;
        if ('0' === $type) {
            $dataList = PurchaseItem::getPurchaseDetailList(
                null
                , null
                , $purchase_sn
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
                , $expire_day
                , $audit_status)
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
                , $expire_day
                , $audit_status)
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
            , 'audit_status' => $audit_status
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
        $purchaseItemData = PurchaseItem::getPurchaseDetailList($id)->get()->toArray();

        if (!$purchaseData) {
            return abort(404);
        }

        //做一陣列 整理各款式商品的入庫人員，將重複的去除
        $inbound_name_arr = [];
        $inbound_names = '';
        if (null != $purchaseItemData && 0 < count($purchaseItemData)) {
            foreach ($purchaseItemData as $item) {
                if (isset($item->inbound_user_names)) {
                    $item_name_arr = explode(',', $item->inbound_user_names);
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
                    if ($payingOrderItem->balance_date) {
                        $hasReceivedDepositPayment = true;
                    }
                } elseif ($payingOrderItem->type === 1) {
                    $hasCreatedFinalPayment = true;
                    $finalPayData = $payingOrderItem;
                    if ($payingOrderItem->balance_date) {
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

    /**
     * @throws ValidationException
     */
    public function update(Request $request, $id)
    {
        $query = $request->query();
        $this->validInputValue($request);

        $taxReq = $request->input('tax');
        $purchaseReq = $request->only('supplier', 'scheduled_date', 'supplier_sn', 'audit_status');
        $purchaseItemReq = $request->only('item_id', 'product_style_id', 'name', 'sku', 'num', 'price', 'memo');
        $purchasePayReq = $request->only('tax', 'logistics_price', 'logistics_memo', 'invoice_num', 'invoice_date');

        //判斷是否有付款單，有則不可新增刪除商品款式
        $purchaseGet = Purchase::where('id', '=', $id)->get()->first();

        //判斷原採購單已審核
        if (null != $purchaseGet && AuditStatus::unreviewed()->value != $purchaseGet->audit_status) {
            $purchase = Purchase::checkInputApprovedDataDirty($id, $taxReq, $purchaseReq, $purchasePayReq);
            if ($purchase->isDirty()) {
                throw ValidationException::withMessages(['item_error' => '已審核，無法再修改']);
            }

            //刪除現有款式
            if (isset($request['del_item_id']) && null != $request['del_item_id']) {
                throw ValidationException::withMessages(['item_error' => '已審核，不可刪除商品款式']);
            }

            if (isset($purchaseItemReq['item_id'])) {
                foreach ($purchaseItemReq['item_id'] as $key => $val) {
                    $itemId = $purchaseItemReq['item_id'][$key];
                    if (null != $itemId) {
                        $purchaseItem = PurchaseItem::checkInputItemDirty($itemId, $purchaseItemReq, $key);
                        if ($purchaseItem->isDirty()) {
                            throw ValidationException::withMessages(['item_error' => '已審核，不可新增修改商品款式']);
                        }
                    } else {
                        throw ValidationException::withMessages(['item_error' => '已審核，不可新增修改商品款式']);
                    }
                }
            }
        }

        $msg = DB::transaction(function () use ($request, $id, $purchaseReq, $purchaseItemReq, $taxReq, $purchasePayReq, $purchaseGet
        ) {
            $repcsCTPD = Purchase::checkToUpdatePurchaseData($id, $purchaseReq, $request->user()->id, $request->user()->name, $taxReq, $purchasePayReq);
            if ($repcsCTPD['success'] == 0) {
                DB::rollBack();
                return $repcsCTPD;
            }
            //刪除現有款式
            if (isset($request['del_item_id']) && null != $request['del_item_id']) {
                $del_item_id_arr = explode(",", $request['del_item_id']);
                $rePcsDI = PurchaseItem::deleteItems($purchaseGet->id, $del_item_id_arr, $request->user()->id, $request->user()->name);
                if ($rePcsDI['success'] == 0) {
                    DB::rollBack();
                    return $rePcsDI;
                }
            }
            if (isset($purchaseItemReq['item_id'])) {
                foreach ($purchaseItemReq['item_id'] as $key => $val) {
                    $itemId = $purchaseItemReq['item_id'][$key];
                    //有值則做更新
                    //itemId = null 代表新資料
                    if (null != $itemId) {
                        $result = PurchaseItem::checkToUpdatePurchaseItemData($itemId, $purchaseItemReq, $key, $request->user()->id, $request->user()->name, $purchasePayReq);
                        if ($result['success'] == 0) {
                            DB::rollBack();
                            return $result;
                        }
                    } else {
                        $result = PurchaseItem::createPurchase(
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
                        if ($result['success'] == 0) {
                            DB::rollBack();
                            return $result;
                        }
                    }
                }
            }
            return ['success' => 1, 'error_msg' => 'all ok'];
        });
        if ($msg['success'] == 0) {
            throw ValidationException::withMessages(['item_error' => $msg['error_msg']]);
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
        $result = Purchase::del($id, $request->user()->id, $request->user()->name);
        if ($result['success'] == 0) {
            wToast($result['error_msg']);
        } else {
            wToast(__('Delete finished.'));
        }
        return redirect(Route('cms.purchase.index'));
    }

    //結案
    public function close(Request $request, $id) {
        $inboundOverviewList = PurchaseInbound::getOverviewInboundList(Event::purchase()->value, $id)->get()->toArray();
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
        $inboundList = PurchaseInbound::getInboundList(['event' => Event::purchase()->value, 'purchase_id' => $id])
            ->orderByDesc('inbound.created_at')
            ->get()->toArray();
        $inboundOverviewList = PurchaseInbound::getOverviewInboundList(Event::purchase()->value, $id)->get()->toArray();

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
            'event_item_id.*' => 'required|numeric',
            'product_style_id.*' => 'required|numeric',
            'inbound_date.*' => 'required|string',
            'inbound_num.*' => 'required|numeric',
            'error_num.*' => 'required|numeric|min:0',
            'status.*' => 'required|numeric|min:0',
            'expiry_date.*' => 'required|string',
        ]);
        $depot_id = $request->input('depot_id');
        $inboundItemReq = $request->only('event_item_id', 'product_style_id', 'inbound_date', 'inbound_num', 'error_num', 'inbound_memo', 'status', 'expiry_date', 'inbound_memo');

        if (isset($inboundItemReq['product_style_id'])) {
            //檢查若輸入實進數量小於0，打負數時備註欄位要必填說明原因
            foreach ($inboundItemReq['product_style_id'] as $key => $val) {
                if (1 > $inboundItemReq['inbound_num'][$key] && true == empty($inboundItemReq['inbound_memo'][$key])) {
                    throw ValidationException::withMessages(['inbound_memo.'.$key => '打負數時備註欄位要必填說明原因']);
                }
            }

            $depot = Depot::where('id', '=', $depot_id)->get()->first();
            $style_arr = PurchaseInbound::getCreateData(Event::purchase()->value, $id, $inboundItemReq['event_item_id'], $inboundItemReq['product_style_id']);

            $result = DB::transaction(function () use ($inboundItemReq, $id, $depot_id, $depot, $request, $style_arr
            ) {
                foreach ($style_arr as $key => $val) {
                    $re = PurchaseInbound::createInbound(
                        Event::purchase()->value,
                        $id,
                        $inboundItemReq['event_item_id'][$key],
                        $inboundItemReq['product_style_id'][$key],
                        $val['item']['title'] . '-'. $val['item']['spec'],
                        $val['unit_cost'],
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
        } else {
            return abort(404);
        }
        $re = PurchaseInbound::delInbound($id, $request->user()->id);
        if ($re['success'] == 0) {
            wToast($re['error_msg']);
        } else {
            wToast(__('Delete finished.'));
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
        $request->merge([
            'id' => $id,
            'type' => request('type'),
        ]);

        $request->validate([
            'id' => 'required|exists:pcs_purchase,id',
            'type' => 'required|in:0,1',
        ]);

        $source_type = app(Purchase::class)->getTable();
        $source_sub_id = null;
        $type = request('type');

        $paying_order = PayingOrder::where([
            'source_type' => $source_type,
            'source_id' => $id,
            'source_sub_id' => $source_sub_id,
            'type' => $type,
            'deleted_at' => null,
        ])->first();

        $validatedReq = $request->except('_token');

        $purchase = Purchase::purchase_item($id)->get();
        foreach ($purchase as $key => $value) {
            $purchase[$key]->purchase_table_items = json_decode($value->purchase_table_items);
        }
        $purchase = $purchase->first();

        //產生付款單
        if ($request->isMethod('POST')) {
            if(! $paying_order){
                if ($validatedReq['type'] === '1') {
                    $totalPrice = self::getPaymentPrice($id)['finalPaymentPrice'];
                } elseif (isset($validatedReq['price'])) {
                    $totalPrice = intval($validatedReq['price']);
                }

                $product_grade = PayableDefault::where('name', '=', 'product')->first()->default_grade_id;
                $logistics_grade = PayableDefault::where('name', '=', 'logistics')->first()->default_grade_id;

                $result = PayingOrder::createPayingOrder(
                    $source_type,
                    $id,
                    $source_sub_id,
                    $request->user()->id,
                    $validatedReq['type'],
                    $product_grade,
                    $logistics_grade,
                    $totalPrice ?? 0,
                    $request['deposit_summary'] ?? '',
                    $request['deposit_memo'] ?? '',
                    $purchase->supplier_id,
                    $purchase->supplier_name,
                    $purchase->supplier_phone,
                    $purchase->supplier_address
                );

                $paying_order = PayingOrder::findOrFail($result['id']);
            }
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
        $logisticsGradeName = AllGrade::find($payingOrderQuery->logistics_grade_id)->eachGrade->code . ' - ' . AllGrade::find($payingOrderQuery->logistics_grade_id)->eachGrade->name;

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

        $pay_off = false;
        $pay_off_date = null;
        $payable_data = PayingOrder::get_payable_detail($paying_order->id);
        if($payingOrderData->price == $payable_data->sum('tw_price') ){
            $pay_off = true;
            if($payingOrderData->price == 0 && $payable_data->count() == 0){
                $pay_off_date = date('Y-m-d', strtotime($payingOrderData->created_at));
            } else {
                $pay_off_date = date('Y-m-d', strtotime($payingOrderData->balance_date));
            }
        }

        if ($accountPayable) {
            $accountant = DB::table('usr_users')
                            ->find($accountPayable->accountant_id_fk, ['name'])
                            ->name;
        }

        // session([
        //     '_url'=>request()->fullUrl()
        // ]);

        return view('cms.commodity.purchase.pay_order', [
            'id' => $id,
            'accountant' => $accountant ?? '',
            'accountPayableId' => $accountPayable->id ?? null,
            'payOrdId' => $payingOrderData->id,
            'type' => ($validatedReq['type'] === '0') ? 'deposit' : 'final',
            'breadcrumb_data' => ['id' => $id, 'sn' => $purchaseData->purchase_sn, 'type' => $validatedReq['type']],
            'formAction' => Route('cms.purchase.index', ['id' => $id,]),
            'supplierUrl' => Route('cms.supplier.edit', ['id' => $supplier->id,]),
            'purchaseData' => $purchaseData,
            'pay_off' => $pay_off,
            'pay_off_date' => $pay_off_date,
            'payingOrderData' => $payingOrderData,
            'productGradeName' => $productGradeName,
            'logisticsGradeName' => $logisticsGradeName,
            'depositPaymentData' => $depositPaymentData,
            'finalPaymentPrice' => $paymentPrice['finalPaymentPrice'],
            'logisticsPrice' => $paymentPrice['logisticsPrice'],
            'purchaseItemData' => $purchaseItemData,
            'payable_data' => $payable_data,
            'chargemen' => $chargemen,
            'undertaker' => $undertaker,
            'appliedCompanyData' => $appliedCompanyData,
            'supplier' => $supplier,
        ]);
    }


    public function po_create(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'acc_transact_type_fk'    => ['required', 'string', 'regex:/^[1-6]$/'],
                'pay_order_type' => ['required', 'string', 'regex:/^(pcs)$/'],
                'pay_order_id' => 'required|exists:pcs_paying_orders,id',
                'is_final_payment' => ['required', 'int', 'regex:/^(0|1)$/']
            ]);
            $req = $request->all();
            $payableType = $req['acc_transact_type_fk'];

            switch ($payableType) {
                case Payment::Cash:
                    PayableCash::storePayableCash($req);
                    break;
                case Payment::Cheque:
                    PayableCheque::storePayableCheque($req);
                    break;
                case Payment::Remittance:
                    PayableRemit::storePayableRemit($req);
                    break;
                case Payment::ForeignCurrency:
                    PayableForeignCurrency::storePayableCurrency($req);
                    break;
                case Payment::AccountsPayable:
                    PayableAccount::storePayablePayableAccount($req);
                    break;
                case Payment::Other:
                    PayableOther::storePayableOther($req);
                    break;
            }

            $paying_order = PayingOrder::find(request('pay_order_id'));
            $pay_list = AccountPayable::where('pay_order_id', request('pay_order_id'))->get();
            if (count($pay_list) > 0 && $paying_order->price == $pay_list->sum('tw_price')) {
                $paying_order->update([
                    'balance_date'=>date("Y-m-d H:i:s"),
                ]);
            }

            if (PayingOrder::find(request('pay_order_id')) && PayingOrder::find(request('pay_order_id'))->balance_date) {
                return redirect()->route('cms.purchase.view-pay-order', [
                    'id' => $req['purchase_id'],
                    'type' => $req['is_final_payment']
                ]);

            } else {
                return redirect()->route('cms.purchase.po-create', [
                    'payOrdId' => request('pay_order_id'),
                    'payOrdType' => 'pcs',
                    'isFinalPay' => request('is_final_payment'),
                    'purchaseId' => $paying_order->source_id
                ]);
            }

        } else {

            $request->validate([
                'payOrdType' => 'required|regex:/^(pcs)$/',
                'payOrdId' => 'required|exists:pcs_paying_orders,id',
                'isFinalPay' => 'required|in:0,1',
                'purchaseId' => 'required|exists:pcs_purchase,id',
            ]);

            $payOrdId = $request['payOrdId'];

            $all_payable_type_data = [
                'payableCash' => [],
                'payableCheque' => [],
                'payableRemit' => [],
                'payableForeignCurrency' => [],
                'payableAccount' => [],
                'payableOther' => [],
            ];

            $paying_order = PayingOrder::findOrFail($payOrdId);

            $product_grade_name = AllGrade::find($paying_order->product_grade_id)->eachGrade->code . ' ' . AllGrade::find($paying_order->product_grade_id)->eachGrade->name;
            $logistics_grade_name = AllGrade::find($paying_order->logistics_grade_id)->eachGrade->code . ' ' . AllGrade::find($paying_order->logistics_grade_id)->eachGrade->name;

            $purchase_item_data = PurchaseItem::getPurchaseItemsByPurchaseId($paying_order->source_id);
            $logistics = Purchase::findOrFail($paying_order->source_id);

            $deposit_payment_data = PayingOrder::getPayingOrdersWithPurchaseID($paying_order->source_id, 0)->first();

            $purchase_data = Purchase::getPurchase($paying_order->source_id)->first();
            $supplier = Supplier::where('id', '=', $purchase_data->supplier_id)->first();
            $currency = DB::table('acc_currency')->find($paying_order->acc_currency_fk);
            if(!$currency){
                $currency = (object)[
                    'name'=>'NTD',
                    'rate'=>1,
                ];
            }

            $paid_paying_order_data = PayingOrder::where(function ($q){
                    $q->where([
                        'source_type'=>app(Purchase::class)->getTable(),
                        'source_id'=>request('purchaseId'),
                        'deleted_at'=>null,
                    ]);

                    if(request('isFinalPay') === '0'){
                        $q->where([
                            'type'=>request('isFinalPay'),
                        ]);
                    }
                })->get();

            // $payable_data = PayingOrder::get_payable_detail($paid_paying_order_data->pluck('id')->toArray());
            $payable_data = AccountPayable::whereIn('pay_order_id', $paid_paying_order_data->pluck('id')->toArray())->get();
            foreach($payable_data as $value){
                if($value->acc_income_type_fk == 4){
                    $value->currency_name = DB::table('acc_currency')->find($value->payable->acc_currency_fk)->name;
                    $value->currency_rate = $value->payable->rate;
                } else {
                    $value->currency_name = 'NTD';
                    $value->currency_rate = 1;
                }
            }
            $tw_price = $paid_paying_order_data->sum('price') - $payable_data->sum('tw_price');

            $total_grades = GeneralLedger::total_grade_list();

            return view('cms.commodity.purchase.po_create', [
                'tw_price' => $tw_price,
                'payable_data' => $payable_data,

                // 'thirdGradesDataList' => $thirdGradesDataList,
                // 'fourthGradesDataList' => $fourthGradesDataList,
                // 'currencyData' => $currencyData,
                // 'paymentStatusList' => $payStatusArray,
                'cashDefault' => PayableDefault::where('name', 'cash')->pluck('default_grade_id')->toArray(),
                'chequeDefault' => PayableDefault::where('name', 'cheque')->pluck('default_grade_id')->toArray(),
                'remitDefault' => PayableDefault::where('name', 'remittance')->pluck('default_grade_id')->toArray(),
                'all_currency' => PayableDefault::getCurrencyOptionData()['selectedCurrencyResult']->toArray(),
                'currencyDefault' => PayableDefault::where('name', 'foreign_currency')->pluck('default_grade_id')->toArray(),
                'accountPayableDefault' => PayableDefault::where('name', 'accounts_payable')->pluck('default_grade_id')->toArray(),
                'otherDefault' => PayableDefault::where('name', 'other')->pluck('default_grade_id')->toArray(),

                'method' => 'create',
                'transactTypeList' => AccountPayable::getTransactTypeList(),
                'chequeStatus' => AccountPayable::getChequeStatus(),
                'formAction' => Route('cms.purchase.po-create'),

                'breadcrumb_data' => ['id' => $paying_order->source_id, 'sn' => $purchase_data->purchase_sn, 'type' => request('isFinalPay')],
                'product_grade_name' => $product_grade_name,
                'logistics_grade_name' => $logistics_grade_name,
                'logistics_price' => $logistics->logistics_price,
                'purchase_item_data' => $purchase_item_data,
                'deposit_payment_data' => $deposit_payment_data,
                'paying_order' => $paying_order,
                'currency' => $currency,
                'type' => request('isFinalPay') === '0' ? 'deposit' : 'final',
                'all_payable_type_data' => $all_payable_type_data,
                'purchase_data' => $purchase_data,
                'supplier' => $supplier,

                'total_grades' => $total_grades,
            ]);
        }
    }


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
        $purchaseLog = PurchaseLog::getData(Event::purchase()->value, $id)->get();
        if (!$purchaseData) {
            return abort(404);
        }

        return view('cms.commodity.purchase.log', [
            'id' => $id,
            'purchaseData' => $purchaseData,
            'purchaseLog' => $purchaseLog,
            'returnAction' => Route('cms.purchase.index', [], true),
            'title' => '採購單',
            'sn' => $purchaseData->purchase_sn,
            'event' => Event::purchase()->value,
            'breadcrumb_data' => $purchaseData->purchase_sn,
        ]);
    }
}

