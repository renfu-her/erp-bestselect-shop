<?php

namespace App\Http\Controllers\Cms;

use App\Enums\Consignment\AuditStatus;
use App\Enums\Delivery\Event;
use App\Enums\Supplier\Payment;
use App\Enums\Payable\ChequeStatus;
use App\Enums\Purchase\ReturnStatus;
use App\Enums\Received\ReceivedMethod;
use App\Enums\Area\Area;

use App\Helpers\IttmsDBB;
use App\Http\Controllers\Controller;

use App\Models\AllGrade;
use App\Models\DayEnd;
use App\Models\Depot;
use App\Models\PayingOrder;
use App\Models\PcsStatisInbound;
use App\Models\Petition;
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
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\PurchaseElementReturn;
use App\Models\Supplier;
use App\Models\User;
use App\Models\GeneralLedger;
use App\Models\PayableDefault;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Enums\Purchase\InboundStatus;

use App\Models\AccountPayable;
use App\Models\Sn;
use App\Models\CrdCreditCard;
use App\Models\ReceivedOrder;
use App\Models\OrderPayCreditCard;
use App\Models\Product;

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
        // $sku = Arr::get($query, 'sku', '');
        $purchase_user_id = Arr::get($query, 'purchase_user_id', []);
        $purchase_sdate = Arr::get($query, 'purchase_sdate', '');
        $purchase_edate = Arr::get($query, 'purchase_edate', '');
        $supplier_id = Arr::get($query, 'supplier_id', '');
        $estimated_depot_id = Arr::get($query, 'estimated_depot_id', '');
        $depot_id = Arr::get($query, 'depot_id', '');
        $inbound_user_id = Arr::get($query, 'inbound_user_id', []);
        $inbound_status = Arr::get($query, 'inbound_status', implode(',', array_keys($all_inbound_status)));
        $inbound_sdate = Arr::get($query, 'inbound_sdate', '');
        $inbound_edate = Arr::get($query, 'inbound_edate', '');
        $expire_day = Arr::get($query, 'expire_day', '');
        $audit_status = Arr::get($query, 'audit_status', null);
        $has_error_num = Arr::get($query, 'has_error_num', 0);

        $inbound_status_arr = [];
        if ('' != $inbound_status) {
            $inbound_status_arr = explode(',', $inbound_status);
        }

        $dataList = PurchaseItem::getPurchaseOverviewList(
            $purchase_sn
            , $title
            , $purchase_user_id
            , $purchase_sdate
            , $purchase_edate
            , $supplier_id
            , $estimated_depot_id
            , $depot_id
            , $inbound_user_id
            , $inbound_status_arr
            , $inbound_sdate
            , $inbound_edate
            , $expire_day
            , $audit_status
            , $has_error_num)
            ->paginate($data_per_page)->appends($query);

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
            , 'estimated_depot_id' => $estimated_depot_id
            , 'depot_id' => $depot_id
            , 'inbound_user_id' => $inbound_user_id
            , 'inbound_status' => $inbound_status
            , 'all_inbound_status' => $all_inbound_status
            , 'inbound_sdate' => $inbound_sdate
            , 'inbound_edate' => $inbound_edate
            , 'expire_day' => $expire_day
            , 'audit_status' => $audit_status
            , 'has_error_num' => $has_error_num
        ]);
    }

    public function create(Request $request)
    {
        $supplierList = Supplier::getSupplierList()->get();
        $depotList = Depot::all()->toArray();
        return view('cms.commodity.purchase.edit', [
            'method' => 'create',
            'supplierList' => $supplierList,
            'depotList' => $depotList,
            'formAction' => Route('cms.purchase.create'),
        ]);
    }

    public function store(Request $request)
    {
        $query = $request->query();
        $this->validInputValue($request);

        $purchaseReq = $request->only('supplier', 'scheduled_date', 'estimated_depot_id', 'supplier_sn');
        $purchaseItemReq = $request->only('product_style_id', 'name', 'sku', 'num', 'price', 'memo');
        $purchasePayReq = $request->only('logistics_price', 'logistics_memo', 'invoice_num', 'invoice_date');

        $estimated_depot_id = null;
        $estimated_depot_name = null;
        if (isset($purchaseReq['estimated_depot_id'])) {
            $depot = Depot::where('id', '=', $purchaseReq['estimated_depot_id'])->first();
            if (isset($depot)) {
                $estimated_depot_id = $depot->id;
                $estimated_depot_name = $depot->name;
            }
        }

        $supplier = Supplier::where('id', '=', $purchaseReq['supplier'])->get()->first();
        $rePcs = Purchase::createPurchase(
            null,
            $purchaseReq['supplier'],
            $supplier->name,
            $supplier->nickname,
            $purchaseReq['supplier_sn'] ?? null,
            $request->user()->id,
            $request->user()->name,
            $purchaseReq['scheduled_date'],
            $estimated_depot_id ?? null,
            $estimated_depot_name ?? null,
            $purchasePayReq['logistics_price'] ?? null,
            $purchasePayReq['logistics_memo'] ?? null,
            $purchasePayReq['invoice_num'] ?? null,
            $purchasePayReq['invoice_date'] ?? null,
            $request->input('note')
        );
        $purchaseID = null;
        if (isset($rePcs['id'])) {
            $purchaseID = $rePcs['id'];
        }

        $result = null;
        $result = IttmsDBB::transaction(function () use ($purchaseItemReq, $rePcs, $request, $purchaseID
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
        $depotList = Depot::all()->toArray();

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
                    if ($payingOrderItem->payment_date) {
                        $hasReceivedDepositPayment = true;
                    }
                } elseif ($payingOrderItem->type === 1) {
                    $hasCreatedFinalPayment = true;
                    $finalPayData = $payingOrderItem;
                    if ($payingOrderItem->payment_date) {
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
            'depotList' => $depotList,
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
            'relation_order' => Petition::getBindedOrder($id, 'B'),
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
        $purchaseReq = $request->only('supplier', 'scheduled_date', 'estimated_depot_id', 'supplier_sn', 'audit_status');
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
                        $purchaseItem = PurchaseItem::checkInputItemDirtyWithoutMemo($itemId, $purchaseItemReq, $key);
                        if ($purchaseItem->isDirty()) {
                            throw ValidationException::withMessages(['item_error' => '已審核，不可新增修改商品款式']);
                        }
                    } else {
                        throw ValidationException::withMessages(['item_error' => '已審核，不可新增修改商品款式']);
                    }
                }
            }
        }
//        dd('end');
        $note = $request->input('note');

        $msg = IttmsDBB::transaction(function () use ($request, $id, $purchaseReq, $purchaseItemReq, $taxReq, $purchasePayReq, $purchaseGet,$note
        ) {
            $repcsCTPD = Purchase::checkToUpdatePurchaseData($id, $purchaseReq, $request->user()->id, $request->user()->name, $taxReq, $purchasePayReq,$note);
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
                        $po = PayingOrder::where([
                            'source_type' => app(Purchase::class)->getTable(),
                            'source_id' => $id,
                            'source_sub_id' => null,
                            'type' => 1,
                        ])->first();
                        if($po && $po->payment_date){
                            $purchaseItem = PurchaseItem::where('id', '=', $itemId)->first();
                            if($purchaseItem){
                                $purchaseItemReq['memo'][$key] = $purchaseItem->memo;
                            }
                        }

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
        $inboundList = PurchaseInbound::getInboundList(['event' => Event::purchase()->value, 'event_id' => $id])
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

            $result = IttmsDBB::transaction(function () use ($inboundItemReq, $id, $depot_id, $depot, $request, $style_arr
            ) {
                $purchase = Purchase::where('id', '=', $id)->first();
                if (false == isset($purchase)) {
                    DB::rollBack();
                    return ['success' => 0, 'error_msg' => "無此採購單 不可入庫"];
                } else if (AuditStatus::veto()->value == $purchase->audit_status) {
                    DB::rollBack();
                    return ['success' => 0, 'error_msg' => "否決後 不可入庫"];
//                //未核可也必須可以入庫
//                } else if (AuditStatus::unreviewed()->value == $purchase->audit_status) {
//                    DB::rollBack();
//                    return ['success' => 0, 'error_msg' => "尚未核可 不可入庫"];
                }
                foreach ($style_arr as $key => $val) {
                    $re = PurchaseInbound::createInbound(
                        Event::purchase()->value,
                        $id,
                        $inboundItemReq['event_item_id'][$key],
                        $inboundItemReq['product_style_id'][$key],
                        $val['item']['title'] . '-'. $val['item']['spec'],
                        $val['sku'],
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
                wToast($result['error_msg'], ['type'=>'danger']);
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
            wToast($re['error_msg'], ['type'=>'danger']);
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
                    $purchase ? $purchase->supplier_name : null,
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
        if($payingOrderData && $payingOrderData->append_po_id){
            $append_po = PayingOrder::find($payingOrderData->append_po_id);
            $payingOrderData->append_po_link = PayingOrder::paying_order_link($append_po->source_type, $append_po->source_id, $append_po->source_sub_id, $append_po->type);
        }
        $payingOrderQuery = PayingOrder::find($payingOrderData->id);
        $productGradeName = AllGrade::find($payingOrderQuery->product_grade_id)->eachGrade->code . ' ' . AllGrade::find($payingOrderQuery->product_grade_id)->eachGrade->name;
        $logisticsGradeName = AllGrade::find($payingOrderQuery->logistics_grade_id)->eachGrade->code . ' ' . AllGrade::find($payingOrderQuery->logistics_grade_id)->eachGrade->name;

        $purchaseItemData = PurchaseItem::getPurchaseItemsByPurchaseId($id);

        $purchaseData = Purchase::getPurchase($id)->first();

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

        $payable_data = PayingOrder::get_payable_detail($paying_order->id);
        $data_status_check = PayingOrder::payable_data_status_check($payable_data);
        $po_count = PayingOrder::where([
                'source_type' => $source_type,
                'source_id' => $id,
                'source_sub_id' => $source_sub_id,
                'deleted_at' => null,
            ])->count();

        if($type == 0 && $po_count == 2) {
            $data_status_check = true;
        }

        if ($accountPayable) {
            $accountant = DB::table('usr_users')
                            ->find($accountPayable->accountant_id_fk, ['name'])
                            ->name;
        }

        $zh_price = num_to_str($paying_order->price);

        $view = 'cms.commodity.purchase.pay_order';
        if (request('action') == 'print') {
            $view = 'doc.print_purchase_order_pay';
        }

        return view($view, [
            'id' => $id,
            'purchase' => $purchase,
            'accountant' => $accountant ?? '',
            'accountPayableId' => $accountPayable->id ?? null,
            'payOrdId' => $payingOrderData->id,
            'type' => ($validatedReq['type'] === '0') ? 'deposit' : 'final',
            'breadcrumb_data' => ['id' => $id, 'sn' => $purchaseData->purchase_sn, 'type' => $validatedReq['type']],
            'formAction' => Route('cms.purchase.index', ['id' => $id,]),
            'purchaseData' => $purchaseData,
            'payingOrderData' => $payingOrderData,
            'productGradeName' => $productGradeName,
            'logisticsGradeName' => $logisticsGradeName,
            'depositPaymentData' => $depositPaymentData,
            'logisticsPrice' => $paymentPrice['logisticsPrice'],
            'purchaseItemData' => $purchaseItemData,
            'payable_data' => $payable_data,
            'data_status_check' => $data_status_check,
            'chargemen' => $chargemen,
            'undertaker' => $undertaker,
            'appliedCompanyData' => $appliedCompanyData,
            'zh_price' => $zh_price,
            'paying_order' => $paying_order,
            'relation_order' => Petition::getBindedOrder($paying_order->id, 'ISG'),
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
                    $request->validate([
                        'cheque.ticket_number'=>'required|unique:acc_payable_cheque,ticket_number,po_delete,status_code|regex:/^[A-Z]{2}[0-9]{7}$/'
                    ]);
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
                    'balance_date' => date('Y-m-d H:i:s'),
                    'payment_date' => $req['payment_date'],
                ]);

                DayEnd::match_day_end_status($req['payment_date'], $paying_order->sn);
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

            $payable_data = PayingOrder::get_payable_detail($paid_paying_order_data->pluck('id')->toArray());

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
                'chequeStatus' => ChequeStatus::get_key_value(),
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
                'purchase_data' => $purchase_data,
                'supplier' => $supplier,

                'total_grades' => $total_grades,
            ]);
        }
    }

    public function payDeposit(Request $request, $id) {
        $purchaseData = Purchase::getPurchase($id)->first();
        // $supplier = Supplier::where('id', '=', $purchaseData->supplier_id)->get()->first();
        // $purchaseChargemanList = PurchaseItem::getPurchaseChargemanList($id)->get();

        // $payList = SupplierPayment::where('supplier_id', '=', $purchaseData->supplier_id)->get()->toArray();
        $payTypeList = [];
        if (isset($payList)) {
            foreach ($payList as $key => $value) {
                array_push($payTypeList, $value['type']);
            }
        }
        // dd($supplier);

        return view('cms.commodity.purchase.receipt', [
            'type' => 'deposit',
            'id' => $id,
            'purchaseData' => $purchaseData,
            // 'supplier' => $supplier,
            'payTypeList' => $payTypeList,
            // 'payList' => $payList,
            // 'purchaseChargemanList' => $purchaseChargemanList,
            'breadcrumb_data' => ['id' => $id, 'sn' => $purchaseData->purchase_sn],
            'formAction' => Route('cms.purchase.pay-order', ['id' => $id, 'type' => '0']),
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

    public function printPurchase(Request $request, $id) {
        $query = $request->query();
        $type = empty($query['type']) ? 'M1': $query['type'];

        $purchaseData = Purchase::getPurchase($id)->first();
        $purchaseItemData = PurchaseItem::getPurchaseDetailList($id)->get()->toArray();
        if (!$purchaseData) {
            return abort(404);
        }

        return view('doc.print_purchase_order', [
            'type' => $type,
            'id' => $id,
            'purchaseData' => $purchaseData,
            'purchaseItemData' => $purchaseItemData,
        ]);
    }


    public function return_list($purchase_id = null)
    {
        if($purchase_id){
            $purchaseData = Purchase::getPurchase($purchase_id)->first();
            // $purchaseItemData = PurchaseItem::getPurchaseDetailList($purchase_id)->get()->toArray();

            if (!$purchaseData) {
                return abort(404);
            }

            $data_list = PurchaseReturn::return_list($purchase_id)->get();

            return view('cms.commodity.purchase.return_list', [
                'breadcrumb_data' => $purchaseData->purchase_sn,
                'id' => $purchase_id,
                'purchaseData' => $purchaseData,
                'data_list' => $data_list,
            ]);

        } else {
            // show all purchase return list index view
        }
    }

    public function return_create(Request $request, $purchase_id)
    {
        if($request->isMethod('post')){
            $request->validate([
                'method' => 'nullable|string',
                'memo' => 'nullable|string',

                'm_item_id' => 'nullable|array',
                'm_item_id.*' => 'nullable|numeric',
                'purchase_item_id.*' => 'nullable|numeric',
                'product_style_id.*' => 'required|string',
                'sku.*' => 'required|string',
                'show.*' => 'filled|bool',
                'product_title.*' => 'required|string',
                'price.*' => 'required|numeric',
                'back_qty.*' => 'required|numeric',
                'mmemo.*' => 'nullable|string',

                'o_item_id' => 'nullable|array',
                'o_item_id.*' => 'nullable|numeric',
                'rgrade_id.*' => 'required_with:btype|numeric',
                'rtitle.*' => 'required|string',
                'rprice.*' => 'required|numeric',
                'rqty.*' => 'required|numeric',
                'rmemo.*' => 'nullable|string',
            ]);

            $data = $request->except('_token');

            $msg = IttmsDBB::transaction(function () use ($data, $purchase_id) {
                $result = PurchaseReturn::create([
                    'sn' => Sn::createSn('pcs_purchase_return', 'BR', 'ymd', 4),
                    'purchase_id' => $purchase_id,
                    'user_id' => Auth::user()->id,
                    'user_name' => Auth::user()->name,
                    'memo' => $data['memo'],
                    'status' => ReturnStatus::add_return()->value
                ]);

                if($result->id){
                    $default_grade_id = PayableDefault::where('name', '=', 'product')->first()->default_grade_id;
                    $time = date('Y-m-d H:i:s');
                    $items = [];

                    // main
                    foreach($data['purchase_item_id'] as $key => $value){
                        $items[] = [
                            'return_id' => $result->id,
                            'purchase_item_id' => $data['purchase_item_id'][$key],
                            'product_style_id' => $data['product_style_id'][$key],
                            'sku' => $data['sku'][$key],
                            'product_title' => $data['product_title'][$key],
                            'price' => $data['price'][$key],
                            'qty' => $data['back_qty'][$key],
                            'memo' => $data['mmemo'][$key],
                            'ro_note' => null,
                            'po_note' => null,
                            'show' => $data['show'][$key],
                            'type' => 0,
                            'grade_id' => $default_grade_id,
                            'created_at' => $time,
                            'updated_at' => $time
                        ];
                    }

                    // other
                    if (array_key_exists('o_item_id', $data)){
                        foreach($data['o_item_id'] as $key => $value){
                            $items[] = [
                                'return_id' => $result->id,
                                'purchase_item_id' => null,
                                'product_style_id' => null,
                                'sku' => null,
                                'product_title' => $data['rtitle'][$key],
                                'price' => $data['rprice'][$key],
                                'qty' => $data['rqty'][$key],
                                'memo' => $data['rmemo'][$key],
                                'ro_note' => null,
                                'po_note' => null,
                                'show' => 1,
                                'type' => 1,
                                'grade_id' => $data['rgrade_id'][$key],
                                'created_at' => $time,
                                'updated_at' => $time
                            ];
                        }
                    }

                    PurchaseReturnItem::insert($items);

                } else {
                    return ['success' => 0, 'error_msg' => '退出單新增失敗'];
                }

                return ['success' => 1, 'return_id' => $result['id']];
            });

            if ($msg['success'] == 0) {
                throw ValidationException::withMessages(['item_error' => $msg['error_msg']]);
            } else {
                wToast('儲存成功');
                return redirect(route('cms.purchase.return_detail', [
                    'return_id' => $msg['return_id'],
                ], true));
            }

        } else {
            $purchaseData = Purchase::getPurchase($purchase_id)->first();
            $purchaseItemData = PurchaseItem::getPurchaseDetailList($purchase_id)->get()->toArray();
            if (!$purchaseData) {
                return abort(404);
            }

            $total_grades = GeneralLedger::total_grade_list();

            return view('cms.commodity.purchase.return_edit', [
                'breadcrumb_data' => ['purchase_id' => $purchase_id, 'purchase_sn' => $purchaseData->purchase_sn],
                'method' => 'create',
                'form_action' => route('cms.purchase.return_create', ['purchase_id' => $purchase_id]),
                'back_url' => route('cms.purchase.return_list', ['purchase_id' => $purchase_id]),
                'main_items' => $purchaseItemData,
                'other_items' => [],
                'total_grades' => $total_grades,
            ]);
        }
    }

    public function return_edit(Request $request, $return_id)
    {
        if($request->isMethod('post')){
            $request->validate([
                'method' => 'nullable|string',
                'memo' => 'nullable|string',

                'm_item_id' => 'nullable|array',
                'm_item_id.*' => 'nullable|numeric',
                'purchase_item_id.*' => 'nullable|numeric',
                'product_style_id.*' => 'required|string',
                'sku.*' => 'required|string',
                'show.*' => 'filled|bool',
                'product_title.*' => 'required|string',
                'price.*' => 'required|numeric',
                'back_qty.*' => 'required|numeric',
                'mmemo.*' => 'nullable|string',

                'o_item_id' => 'nullable|array',
                'o_item_id.*' => 'nullable|numeric',
                'rgrade_id.*' => 'required_with:btype|numeric',
                'rtitle.*' => 'required|string',
                'rprice.*' => 'required|numeric',
                'rqty.*' => 'required|numeric',
                'rmemo.*' => 'nullable|string',
            ]);

            $data = $request->except('_token');

            $msg = IttmsDBB::transaction(function () use ($data, $return_id) {
                $result = PurchaseReturn::where('id', '=', $return_id)->update([
                    'memo' => $data['memo'],
                ]);

                if($result){
                    $default_grade_id = PayableDefault::where('name', '=', 'product')->first()->default_grade_id;
                    $time = date('Y-m-d H:i:s');
                    $items = [];

                    // main
                    foreach($data['purchase_item_id'] as $key => $value){
                        PurchaseReturnItem::where('id', '=', $data['m_item_id'][$key])->update([
                            'product_title' => $data['product_title'][$key],
                            'price' => $data['price'][$key],
                            'qty' => $data['back_qty'][$key],
                            'memo' => $data['mmemo'][$key],
                            'show' => $data['show'][$key],
                        ]);
                    }

                    // other
                    $dArray = array_diff(
                        PurchaseReturnItem::where([
                            'return_id' => $return_id,
                            'type' => 1,
                        ])->pluck('id')->toArray(),
                        array_intersect_key(request('o_item_id'), request('rgrade_id'))
                    );
                    if($dArray) PurchaseReturnItem::destroy($dArray);

                    foreach(request('rgrade_id') as $key => $value){
                        if(request('o_item_id')[$key]){
                            PurchaseReturnItem::find(request('o_item_id')[$key])->update([
                                'product_title' => $data['rtitle'][$key],
                                'price' => $data['rprice'][$key],
                                'qty' => $data['rqty'][$key],
                                'memo' => $data['rmemo'][$key],
                            ]);

                        } else {
                            PurchaseReturnItem::create([
                                'return_id' => $return_id,
                                'purchase_item_id' => null,
                                'product_style_id' => null,
                                'sku' => null,
                                'product_title' => $data['rtitle'][$key],
                                'price' => $data['rprice'][$key],
                                'qty' => $data['rqty'][$key],
                                'memo' => $data['rmemo'][$key],
                                'ro_note' => null,
                                'po_note' => null,
                                'show' => 1,
                                'type' => 1,
                                'grade_id' => $data['rgrade_id'][$key],
                            ]);
                        }
                    }

                } else {
                    return ['success' => 0, 'error_msg' => '退出單更新失敗'];
                }

                return ['success' => 1, 'return_id' => $return_id];
            });

            if ($msg['success'] == 0) {
                throw ValidationException::withMessages(['item_error' => $msg['error_msg']]);
            } else {
                wToast('儲存成功');
                return redirect(route('cms.purchase.return_detail', [
                    'return_id' => $msg['return_id'],
                ], true));
            }

        } else {
            $return = PurchaseReturn::findOrFail($return_id);
            $return_main_item = PurchaseReturnItem::return_item_list($return_id, null, 0)->get()->toArray();
            $return_other_item = PurchaseReturnItem::return_item_list($return_id, null, 1)->get()->toArray();

            $purchaseData = Purchase::getPurchase($return->purchase_id)->first();
            if (!$purchaseData) {
                return abort(404);
            }

            $total_grades = GeneralLedger::total_grade_list();

            return view('cms.commodity.purchase.return_edit', [
                'breadcrumb_data' => ['purchase_id' => $return->purchase_id, 'purchase_sn' => $purchaseData->purchase_sn],
                'method' => 'edit',
                'form_action' => route('cms.purchase.return_edit', ['return_id' => $return_id]),
                'back_url' => route('cms.purchase.return_list', ['purchase_id' => $return->purchase_id]),
                'return' => $return,
                'main_items' => $return_main_item,
                'other_items' => $return_other_item,
                'total_grades' => $total_grades,
            ]);
        }
    }

    public function return_detail($return_id)
    {
        $return = PurchaseReturn::findOrFail($return_id);
        $return_main_item = PurchaseReturnItem::return_item_list($return_id, 1, 0)->get();
        $return_other_item = PurchaseReturnItem::return_item_list($return_id, 1, 1)->get()->toArray();

        $purchaseData = Purchase::getPurchase($return->purchase_id)->first();
        if (!$purchaseData) {
            return abort(404);
        }

        $contact_tel = null;
        $contact_address = null;
        if($purchaseData->supplier_id){
            $supplier = Supplier::find($purchaseData->supplier_id);
            if($supplier) {
                $contact_tel = $supplier->contact_tel . ($supplier->extension ? ' # ' . $supplier->extension : '');
                $contact_address = $supplier->contact_address;
            }
        }

        // audited
        $audited_item = [];
        if($return->inbound_date){
            foreach($return_main_item as $r_value){
                $audited_item[] = (object)[
                    'product_title' => $r_value->product_title,
                    'sku' => $r_value->sku,
                    'audited_items' => PurchaseElementReturn::audited_item_list(null, null, null, $return_id, $r_value->id)->get()->toArray(),
                ];
            }
        }

        $edit_check = in_array($return->status, ['add_return', 'del_return_inbound']) ? true : false;

        //判斷是否有收款單
        $received_order = ReceivedOrder::where([
            'source_type'=>app(PurchaseReturn::class)->getTable(),
            'source_id'=>$return->id,
        ])->first();

        $breadcrumb_data = [
            'purchase_sn' => $purchaseData->purchase_sn,
            'purchase_id' => $return->purchase_id,
        ];

        return view('cms.commodity.purchase.return_detail', [
            'breadcrumb_data' => $breadcrumb_data,
            'return' => $return,
            'return_main_item' => $return_main_item->toArray(),
            'return_other_item' => $return_other_item,
            'purchaseData' => $purchaseData,
            'contact_tel' => $contact_tel,
            'contact_address' => $contact_address,
            'audited_item' => $audited_item,
            'edit_check' => $edit_check,
            'received_order' => $received_order,
        ]);
    }

    public function return_delete($return_id)
    {
        //判斷是否有收款單
        $received_order = ReceivedOrder::where([
            'source_type'=>app(PurchaseReturn::class)->getTable(),
            'source_id'=>$return_id,
        ])->first();
        if($received_order) {
            wToast('已有收款單 無法刪除', ['type' => 'danger']);
            return redirect()->back();
        }

        $return = PurchaseReturn::findOrFail($return_id);
        if($return->inbound_date){
            wToast('退出入庫已審核 無法刪除', ['type' => 'danger']);
            return redirect()->back();
        }

        $target = PurchaseReturn::delete_return($return_id);

        if($target){
            wToast('刪除完成');
        } else {
            wToast('刪除失敗', ['type'=>'danger']);
        }

        return redirect()->back();
    }

    public function print_return($return_id)
    {
        $return = PurchaseReturn::findOrFail($return_id);
        $return_main_item = PurchaseReturnItem::return_item_list($return_id, 1, 0)->get();
        $return_other_item = PurchaseReturnItem::return_item_list($return_id, 1, 1)->get()->toArray();

        $purchaseData = Purchase::getPurchase($return->purchase_id)->first();
        if (!$purchaseData) {
            return abort(404);
        }

        $contact_tel = null;
        $contact_address = null;
        if($purchaseData->supplier_id){
            $supplier = Supplier::find($purchaseData->supplier_id);
            if($supplier) {
                $contact_tel = $supplier->contact_tel . ($supplier->extension ? ' # ' . $supplier->extension : '');
                $contact_address = $supplier->contact_address;
            }
        }

        return view('doc.print_purchase_return', [
            'user_name' => Auth::user()->name,
            'return' => $return,
            'return_main_item' => $return_main_item->toArray(),
            'return_other_item' => $return_other_item,
            'purchaseData' => $purchaseData,
            'contact_tel' => $contact_tel,
            'contact_address' => $contact_address,
        ]);
    }

    public function return_inbound(Request $request, $return_id)
    {
        if($request->isMethod('post')){
            $request->validate([
                'inbound_id.*' => 'nullable|numeric',
                'purchase_id.*' => 'nullable|numeric',
                'purchase_item_id.*' => 'nullable|numeric',
                'return_item_id.*' => 'nullable|numeric',
                'product_style_id.*' => 'nullable|numeric',
                'product_title.*' => 'nullable|string',
                'real_rq.*' => 'nullable|numeric',
                'sub_rq.*' => 'nullable|numeric',
                'return_qty.*' => 'nullable|numeric',
                'memo.*' => 'nullable|string',
            ]);

            $data = $request->except('_token');

            $msg = IttmsDBB::transaction(function () use ($data, $return_id) {
                foreach($data['inbound_id'] as $key => $value){
                    $parm = [
                        'inbound_id' => $data['inbound_id'][$key],
                    ];
                    $inbound = PurchaseInbound::getInboundList($parm)->get()->first();
                    if($data['return_qty'][$key] > ($inbound->inbound_num - $inbound->shipped_num)){
                        return ['success' => 0, 'error_msg' => '退出入庫審核失敗，' . $inbound->product_title . ' 目前庫存小於退出數量'];
                    }
                    if($data['real_rq'][$key] != $data['sub_rq'][$key]){
                        return ['success' => 0, 'error_msg' => '退出入庫審核失敗，' . $inbound->product_title . ' 審核退出數量加總後不等於退出單數量'];
                    }
                }

                $time = date('Y-m-d H:i:s');
                $items = [];
                $log_items = [];
                foreach($data['inbound_id'] as $key => $value){
                    $items[] = [
                        'inbound_id' => $data['inbound_id'][$key],
                        'purchase_id' => $data['purchase_id'][$key],
                        'purchase_item_id' => $data['purchase_item_id'][$key],
                        'return_id' => $return_id,
                        'return_item_id' => $data['return_item_id'][$key],
                        'qty' => $data['return_qty'][$key],
                        'memo' => $data['memo'][$key],
                        'created_at' => $time,
                        'updated_at' => $time
                    ];

                    $log_items[] = [
                        'event_parent_id' => $data['purchase_id'][$key],
                        'product_style_id' => $data['product_style_id'][$key],
                        'event' => 'purchase',
                        'event_id' => $data['purchase_item_id'][$key],
                        'extra_id' => 0,
                        'feature' => ReturnStatus::add_return_inbound()->value,
                        'inbound_id' => $data['inbound_id'][$key],
                        'product_title' => $data['product_title'][$key],
                        'prd_type' => 'p',
                        'qty' => $data['return_qty'][$key] * -1,
                        'user_id' => Auth::user()->id,
                        'user_name' => Auth::user()->name,
                        'note' => $data['memo'][$key],
                        'created_at' => $time,
                        'updated_at' => $time
                    ];

                    // PurchaseInbound::where('id', $data['inbound_id'][$key])->update([
                    //     'scrap_num' => DB::raw('scrap_num + ' . $data['return_qty'][$key])
                    // ]);
                    $inbound_item = PurchaseInbound::where('id', $data['inbound_id'][$key])->first();
                    PurchaseInbound::where('id', $data['inbound_id'][$key])->increment('scrap_num', $data['return_qty'][$key]);
                    PcsStatisInbound::updateData($inbound_item->event, $data['product_style_id'][$key], $inbound_item->depot_id, $data['return_qty'][$key] * -1);
                }

                $i_res = PurchaseElementReturn::insert($items);

                if($i_res){
                    $u_res = PurchaseReturn::where('id', '=', $return_id)->update([
                        'inbound_user_id' => Auth::user()->id,
                        'inbound_user_name' => Auth::user()->name,
                        'inbound_date' => $time,
                        'status' => ReturnStatus::add_return_inbound()->value,
                    ]);

                    if($u_res){
                        //寫入LOG
                        $i_log_res = PurchaseLog::insert($log_items);

                        if($i_log_res){
                            return ['success' => 1];
                        }
                    }
                }

                DB::rollBack();
                return ['success' => 0, 'error_msg' => '退出入庫審核失敗'];
            });

            if ($msg['success'] == 0) {
                throw ValidationException::withMessages(['error_msg' => $msg['error_msg']]);
            } else {

                $return = PurchaseReturn::findOrFail($return_id);
                // $return_main_item = PurchaseReturnItem::return_item_list($return_id, 1, 0)->get();

                // $parm = [
                //     'event' => Event::purchase()->value,
                //     'event_id' => $return->purchase_id,
                //     'event_item_id' => $return_main_item->pluck('purchase_item_id')->toArray()
                // ];
                // $inbound_list = PurchaseInbound::getInboundList($parm);
                // foreach($return_main_item as $r_value){
                //     $r_value->inbound = $inbound_list->where('inbound.event_item_id', $r_value->purchase_item_id)->get()->toArray();
                //     $r_value->inbound_num = $inbound_list->where('inbound.event_item_id', $r_value->purchase_item_id)->sum('inbound.inbound_num');
                // }

                wToast('退出入庫審核成功');
                return redirect(route('cms.purchase.return_list', ['purchase_id' => $return->purchase_id]));
            }

        } else {
            $return = PurchaseReturn::findOrFail($return_id);
            $return_main_item = PurchaseReturnItem::return_item_list($return_id, 1, 0)->get();

            $purchaseData = Purchase::getPurchase($return->purchase_id)->first();
            if (!$purchaseData) {
                return abort(404);
            }

            $parm = [
                'event' => Event::purchase()->value,
                'event_id' => $return->purchase_id,
                'event_item_id' => $return_main_item->pluck('purchase_item_id')->toArray()
            ];
            foreach($return_main_item as $r_value){
                $inbound_list = PurchaseInbound::getInboundList($parm);
                $r_value->inbound = $inbound_list->where('inbound.event_item_id', $r_value->purchase_item_id)->get()->toArray();
                $r_value->inbound_num = $inbound_list->where('inbound.event_item_id', $r_value->purchase_item_id)->sum('inbound.inbound_num');
            }

            return view('cms.commodity.purchase.return_inbound', [
                'breadcrumb_data' => ['purchase_id' => $return->purchase_id, 'purchase_sn' => $purchaseData->purchase_sn],
                'form_action' => route('cms.purchase.return_inbound', ['return_id' => $return_id]),
                'return' => $return,
                'return_main_item' => $return_main_item,
            ]);
        }
    }

    public function return_inbound_delete($return_id)
    {
        $audited_items = PurchaseElementReturn::audited_item_list(null, null, null, $return_id, null)->get()->toArray();

        if (count($audited_items) > 0) {
            $msg = IttmsDBB::transaction(function () use ($return_id, $audited_items) {
                $time = date('Y-m-d H:i:s');
                $log_items = [];
                foreach($audited_items as $key => $value){
                    $log_items[] = [
                        'event_parent_id' => $value->purchase_id,
                        'product_style_id' => $value->product_style_id,
                        'event' => 'purchase',
                        'event_id' => $value->purchase_item_id,
                        'extra_id' => 0,
                        'feature' => ReturnStatus::del_return_inbound()->value,
                        'inbound_id' => $value->inbound_id,
                        'product_title' => $value->product_title,
                        'prd_type' => 'p',
                        'qty' => $value->qty,
                        'user_id' => Auth::user()->id,
                        'user_name' => Auth::user()->name,
                        'note' => null,
                        'created_at' => $time,
                        'updated_at' => $time
                    ];

                    PurchaseInbound::where('id', $value->inbound_id)->decrement('scrap_num', $value->qty);
                }

                $d_res = PurchaseElementReturn::where('return_id', $return_id)->delete();

                if($d_res){
                    $u_res = PurchaseReturn::where('id', '=', $return_id)->update([
                        'inbound_user_id' => null,
                        'inbound_user_name' => null,
                        'inbound_date' => null,
                        'status' => ReturnStatus::del_return_inbound()->value,
                    ]);

                    if($u_res){
                        //寫入LOG
                        $i_log_res = PurchaseLog::insert($log_items);

                        if($i_log_res){
                            return ['success' => 1];
                        }
                    }
                }

                DB::rollBack();
                return ['success' => 0, 'error_msg' => '刪除退出入庫審核失敗'];
            });

            if ($msg['success'] == 0) {
                wToast($msg['error_msg'], ['type' => 'danger']);
                throw ValidationException::withMessages(['error_msg' => $msg['error_msg']]);
            } else {
                wToast('刪除退出入庫審核成功');
                return redirect()->back();
            }

        } else {
            throw ValidationException::withMessages(['error_msg' => '無可退出入庫數量']);
        }
    }

    public function ro_edit($return_id)
    {
        $return_id = request('return_id');

        $return = PurchaseReturn::findOrFail($return_id);
        $return_item = PurchaseReturnItem::return_item_list($return_id, 1, null)->get();

        $purchaseData = Purchase::getPurchase($return->purchase_id)->first();
        if (!$purchaseData) {
            return abort(404);
        }

        $source_type = app(PurchaseReturn::class)->getTable();
        $ro_collection = ReceivedOrder::where([
            'source_type' => $source_type,
            'source_id' => $return_id,
        ]);

        $ro_data = $ro_collection->get();
        $received_data = ReceivedOrder::get_received_detail($ro_data->pluck('id')->toArray());

        $tw_price = $return_item->sum('sub_total');
        if ($ro_data->count() > 0) {
            $tw_price = $ro_data->sum('price') - $received_data->sum('tw_price');
        }

        if ($tw_price == 0) {
            return redirect()->back();
        }

        $defaultData = [];
        foreach (ReceivedMethod::asArray() as $receivedMethod) {
            $defaultData[$receivedMethod] = DB::table('acc_received_default')
                ->where('name', '=', $receivedMethod)
                ->doesntExistOr(function () use ($receivedMethod) {
                    return DB::table('acc_received_default')
                        ->where('name', '=', $receivedMethod)
                        ->select('default_grade_id')
                        ->get();
                });
        }

        $total_grades = GeneralLedger::total_grade_list();

        $allGradeArray = [];
        // $allGrade = AllGrade::all();
        // $gradeModelArray = GradeModelClass::asSelectArray();

        // foreach ($allGrade as $grade) {
        //     $allGradeArray[$grade->id] = [
        //         'grade_id' => $grade->id,
        //         'grade_num' => array_keys($gradeModelArray, $grade->grade_type)[0],
        //         'code' => $grade->eachGrade->code,
        //         'name' => $grade->eachGrade->name,
        //     ];
        // }

        foreach ($total_grades as $grade) {
            $allGradeArray[$grade['primary_id']] = $grade;
        }
        $defaultArray = [];
        foreach ($defaultData as $recMethod => $ids) {
            // 收款方式若沒有預設、或是方式為「其它」，則自動帶入所有會計科目
            if ($ids !== true &&
                $recMethod !== 'other') {
                foreach ($ids as $id) {
                    $defaultArray[$recMethod][$id->default_grade_id] = [
                        // 'methodName' => $recMethod,
                        'method' => ReceivedMethod::getDescription($recMethod),
                        'grade_id' => $id->default_grade_id,
                        'grade_num' => $allGradeArray[$id->default_grade_id]['grade_num'],
                        'code' => $allGradeArray[$id->default_grade_id]['code'],
                        'name' => $allGradeArray[$id->default_grade_id]['name'],
                    ];
                }
            } else {
                if ($recMethod == 'other') {
                    $defaultArray[$recMethod] = $allGradeArray;
                } else {
                    $defaultArray[$recMethod] = [];
                }
            }
        }

        $currencyDefault = DB::table('acc_currency')
            ->leftJoin('acc_received_default', 'acc_currency.received_default_fk', '=', 'acc_received_default.id')
            ->select(
                'acc_currency.name as currency_name',
                'acc_currency.id as currency_id',
                'acc_currency.rate',
                'default_grade_id',
                'acc_received_default.name as method_name'
            )
            ->orderBy('acc_currency.id')
            ->get();
        $currencyDefaultArray = [];
        foreach ($currencyDefault as $default) {
            $currencyDefaultArray[$default->default_grade_id][] = [
                'currency_id' => $default->currency_id,
                'currency_name' => $default->currency_name,
                'rate' => $default->rate,
                'default_grade_id' => $default->default_grade_id,
            ];
        }

        $card_type = CrdCreditCard::distinct('title')->groupBy('title')->orderBy('id', 'asc')->pluck('title', 'id')->toArray();

        $checkout_area = Area::get_key_value();

        return view('cms.commodity.purchase.ro_edit', [
            'defaultArray' => $defaultArray,
            'currencyDefaultArray' => $currencyDefaultArray,
            'tw_price' => $tw_price,
            'receivedMethods' => ReceivedMethod::asSelectArray(),
            'formAction' => Route('cms.purchase.ro-store', ['return_id' => $return_id]),

            'breadcrumb_data' => [
                'purchase_sn' => $purchaseData->purchase_sn,
                'purchase_id' => $return->purchase_id,
                'return_id' => $return->id,
            ],
            'return' => $return,
            'return_item' => $return_item,
            'purchaseData' => $purchaseData,

            'ro_data' => $ro_data,
            'received_data' => $received_data,
            'card_type' => $card_type,
            'checkout_area' => $checkout_area,
        ]);
    }

    public function ro_store(Request $request, $return_id)
    {
        $source_id = $return_id;
        $source_type = app(PurchaseReturn::class)->getTable();

        $request->merge([
            'id' => $source_id
        ]);
        $request->validate([
            'id' => 'required|exists:' . $source_type . ',id',
            'acc_transact_type_fk' => 'required|string|in:' . implode(',', ReceivedMethod::asArray()),
            'tw_price' => 'required|numeric',
            request('acc_transact_type_fk') => 'required|array',
            request('acc_transact_type_fk') . '.grade' => 'required|exists:acc_all_grades,id',
            'summary' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $data = $request->except('_token');
        $ro_collection = ReceivedOrder::where([
            'source_type' => $source_type,
            'source_id' => $source_id,
        ]);
        if (!$ro_collection->first()) {
            ReceivedOrder::create_received_order($source_type, $source_id);
        }
        $received_order_id = $ro_collection->first()->id;

        DB::beginTransaction();

        try {
            // 'credit_card'
            if ($data['acc_transact_type_fk'] == ReceivedMethod::CreditCard) {
                $card_type = CrdCreditCard::distinct('title')->groupBy('title')->orderBy('id', 'asc')->pluck('title', 'id')->toArray();

                $checkout_area = Area::get_key_value();

                $data[$data['acc_transact_type_fk']] = [
                    'cardnumber' => $data[$data['acc_transact_type_fk']]['cardnumber'],
                    'authamt' => $data['tw_price'] ?? 0,
                    'checkout_date' => $data[$data['acc_transact_type_fk']]['checkout_date'] ?? null, // date('Y-m-d H:i:s')
                    'card_type_code' => $data[$data['acc_transact_type_fk']]['card_type_code'] ?? null,
                    'card_type' => $card_type[$data[$data['acc_transact_type_fk']]['card_type_code']] ?? null,
                    'card_owner_name' => $data[$data['acc_transact_type_fk']]['card_owner_name'] ?? null,
                    'authcode' => $data[$data['acc_transact_type_fk']]['authcode'] ?? null,
                    'all_grades_id' => $data[$data['acc_transact_type_fk']]['grade'],
                    'checkout_area_code' => 'taipei', // $data[$data['acc_transact_type_fk']]['credit_card_area_code']
                    'checkout_area' => '台北', // $checkout_area[$data[$data['acc_transact_type_fk']]['credit_card_area_code']]
                    'installment' => $data[$data['acc_transact_type_fk']]['installment'] ?? 'none',
                    'status_code' => 0,
                    'card_nat' => 'local',
                    'checkout_mode' => 'offline',
                ];

                $data[$data['acc_transact_type_fk']]['grade'] = $data[$data['acc_transact_type_fk']]['all_grades_id'];

                $EncArray['more_info'] = $data[$data['acc_transact_type_fk']];

            } else if ($data['acc_transact_type_fk'] == ReceivedMethod::Cheque) {
                $request->validate([
                    request('acc_transact_type_fk') . '.ticket_number' => 'required|unique:acc_received_cheque,ticket_number,ro_delete,status_code|regex:/^[A-Z]{2}[0-9]{7}$/',
                ]);
            }

            $result_id = ReceivedOrder::store_received_method($data);

            $parm = [];
            $parm['received_order_id'] = $received_order_id;
            $parm['received_method'] = $data['acc_transact_type_fk'];
            $parm['received_method_id'] = $result_id;
            $parm['grade_id'] = $data[$data['acc_transact_type_fk']]['grade'];
            $parm['price'] = $data['tw_price'];
            // $parm['accountant_id_fk'] = auth('user')->user()->id;
            $parm['summary'] = $data['summary'];
            $parm['note'] = $data['note'];
            ReceivedOrder::store_received($parm);

            if ($data['acc_transact_type_fk'] == ReceivedMethod::CreditCard) {
                OrderPayCreditCard::create_log(app(PurchaseReturn::class)->getTable(), $source_id, (object) $EncArray);
            }

            DB::commit();
            wToast(__('收款單儲存成功'));

        } catch (\Exception $e) {
            DB::rollback();
            wToast(__('收款單儲存失敗'), ['type' => 'danger']);
        }

        if (ReceivedOrder::find($received_order_id) && ReceivedOrder::find($received_order_id)->balance_date) {
            return redirect()->route('cms.purchase.return_detail', [
                'return_id' => $source_id,
            ]);

        } else {
            return redirect()->route('cms.purchase.ro-edit', [
                'return_id' => $source_id,
            ]);
        }
    }

    public function ro_receipt($return_id)
    {
        $ro_collection = ReceivedOrder::where([
            'source_type' => app(PurchaseReturn::class)->getTable(),
            'source_id' => $return_id,
        ]);

        $ro_data = $ro_collection->get();
        if (count($ro_data) == 0 || !$ro_collection->first()->balance_date) {
            return abort(404);
        }

        $return = PurchaseReturn::findOrFail($return_id);
        $return_item = PurchaseReturnItem::return_item_list($return_id, 1, null)->get();

        $purchaseData = Purchase::getPurchase($return->purchase_id)->first();
        if (!$purchaseData) {
            return abort(404);
        }

        $product_qc = $return_item->pluck('product_user_name')->toArray();
        $product_qc = array_filter(array_unique($product_qc), 'strlen');

        asort($product_qc);

        $received_data = ReceivedOrder::get_received_detail($ro_data->pluck('id')->toArray());
        $data_status_check = ReceivedOrder::received_data_status_check($received_data);

        $undertaker = User::find($ro_collection->first()->usr_users_id);

        // $accountant = User::whereIn('id', $received_data->pluck('accountant_id_fk')->toArray())->get();
        // $accountant = array_unique($accountant->pluck('name')->toArray());
        // asort($accountant);
        $accountant = User::find($ro_collection->first()->accountant_id) ? User::find($ro_collection->first()->accountant_id)->name : null;

        $zh_price = num_to_str($ro_collection->first()->price);

        $view = 'cms.commodity.purchase.ro_receipt';
        if (request('action') == 'print') {
            // 列印－收款單
            $view = 'doc.print_purchase_ro';
        }
        return view($view, [
            'breadcrumb_data' => [
                'purchase_sn' => $purchaseData->purchase_sn,
                'purchase_id' => $return->purchase_id,
                'return_id' => $return->id,
            ],
            'return' => $return,
            'return_item' => $return_item,
            'purchaseData' => $purchaseData,

            'received_order' => $ro_collection->first(),
            'received_data' => $received_data,
            'data_status_check' => $data_status_check,
            'undertaker' => $undertaker,
            'product_qc' => implode(',', $product_qc),
            // 'accountant'=>implode(',', $accountant),
            'accountant' => $accountant,
            'zh_price' => $zh_price,
            'relation_order' => Petition::getBindedOrder($ro_collection->first()->id, 'MSG'),
        ]);
    }

    public function ro_review(Request $request, $return_id)
    {
        $request->merge([
            'id' => $return_id,
        ]);
        $request->validate([
            'id' => 'required|exists:pcs_purchase_return,id',
        ]);

        $ro_collection = ReceivedOrder::where([
            'source_type' => app(PurchaseReturn::class)->getTable(),
            'source_id' => $return_id,
        ]);

        $ro_data = $ro_collection->get();
        if (count($ro_data) == 0 || !$ro_collection->first()->balance_date) {
            return abort(404);
        }

        $received_order = $ro_collection->first();

        if ($request->isMethod('post')) {
            $request->validate([
                'receipt_date' => 'required|date_format:"Y-m-d"',
                'invoice_number' => 'nullable|string',
            ]);

            DB::beginTransaction();

            try {
                $update = [];
                $update['accountant_id'] = auth('user')->user()->id;
                $update['receipt_date'] = request('receipt_date');
                if (request('invoice_number')) {
                    $update['invoice_number'] = request('invoice_number');
                }
                $received_order->update($update);

                if (is_array(request('received_method'))) {
                    $unique_m = array_unique(request('received_method'));

                    foreach ($unique_m as $m_value) {
                        if (in_array($m_value, ReceivedMethod::asArray()) && is_array(request($m_value))) {
                            $req = request($m_value);
                            foreach ($req as $r) {
                                $r['received_method'] = $m_value;
                                ReceivedOrder::update_received_method($r);
                            }
                        }
                    }
                }

                DayEnd::match_day_end_status(request('receipt_date'), $received_order->sn);

                DB::commit();
                wToast(__('入帳日期更新成功'));

                return redirect()->route('cms.purchase.ro-receipt', ['return_id' => request('return_id')]);

            } catch (\Exception $e) {
                DB::rollback();
                wToast(__('入帳日期更新失敗'), ['type' => 'danger']);

                return redirect()->back();
            }

        } else if ($request->isMethod('get')) {
            $received_data = ReceivedOrder::get_received_detail($ro_data->pluck('id')->toArray());
            $data_status_check = ReceivedOrder::received_data_status_check($received_data);

            if ($received_order->receipt_date) {
                if($data_status_check){
                    return redirect()->back();
                }

                DayEnd::match_day_end_status($received_order->receipt_date, $received_order->sn);

                $received_order->update([
                    'accountant_id' => null,
                    'receipt_date' => null,
                ]);

                wToast(__('入帳日期已取消'));
                return redirect()->route('cms.purchase.ro-receipt', ['return_id'=>request('return_id')]);

            } else {
                $return = PurchaseReturn::findOrFail($return_id);
                $return_item = PurchaseReturnItem::return_item_list($return_id, 1, null)->get();

                $purchaseData = Purchase::getPurchase($return->purchase_id)->first();
                if (!$purchaseData) {
                    return abort(404);
                }

                $undertaker = User::find($received_order->usr_users_id);

                $debit = [];
                $credit = [];

                // 收款項目
                foreach ($received_data as $value) {
                    $name = $value->received_method_name . ' ' . $value->summary . '（' . $value->account->code . ' ' . $value->account->name . '）';
                    // GeneralLedger::classification_processing($debit, $credit, $value->master_account->code, $name, $value->tw_price, 'r', 'received');

                    $tmp = [
                        'account_code' => $value->account->code,
                        'name' => $name,
                        'price' => $value->tw_price,
                        'type' => 'r',
                        'd_type' => 'received',

                        'account_name' => $value->account->name,
                        'method_name' => $value->received_method_name,
                        'summary' => $value->summary,
                        'note' => $value->note,
                        'product_title' => null,
                        'del_even' => null,
                        'del_category_name' => null,
                        'product_price' => null,
                        'product_qty' => null,
                        'product_owner' => null,
                        'discount_title' => null,
                        'payable_type' => null,

                        'received_info' => $value,
                    ];
                    GeneralLedger::classification_processing($debit, $credit, $tmp);
                }

                // 商品
                foreach ($return_item as $value) {
                    $name = $value->grade_code . ' ' . $value->grade_name . ' - ' . $value->product_title . '（' . $value->price . ' * ' . $value->qty . '）';
                    // GeneralLedger::classification_processing($debit, $credit, $product_master_account->code, $name, $value->product_origin_price, 'r', 'product');

                    $tmp = [
                        'account_code' => $value->grade_code,
                        'name' => $name,
                        'price' => $value->sub_total,
                        'type' => 'r',
                        'd_type' => 'product',

                        'account_name' => $value->grade_name,
                        'method_name' => null,
                        'summary' => $value->summary ?? null,
                        'note' => $value->note ?? null,
                        'product_title' => $value->product_title,
                        'del_even' => $value->del_even ?? null,
                        'del_category_name' => $value->del_category_name ?? null,
                        'product_price' => $value->price,
                        'product_qty' => $value->qty,
                        'product_owner' => null,
                        'discount_title' => null,
                        'payable_type' => null,
                        'received_info' => null,
                    ];
                    GeneralLedger::classification_processing($debit, $credit, $tmp);
                }

                $card_type = CrdCreditCard::distinct('title')->groupBy('title')->orderBy('id', 'asc')->pluck('title', 'id')->toArray();

                $checkout_area = Area::get_key_value();

                // grade process start
                $defaultData = [];
                foreach (ReceivedMethod::asArray() as $receivedMethod) {
                    $defaultData[$receivedMethod] = DB::table('acc_received_default')->where('name', '=', $receivedMethod)
                        ->doesntExistOr(function () use ($receivedMethod) {
                            return DB::table('acc_received_default')->where('name', '=', $receivedMethod)
                                ->select('default_grade_id')
                                ->get();
                        });
                }

                $total_grades = GeneralLedger::total_grade_list();
                $allGradeArray = [];

                foreach ($total_grades as $grade) {
                    $allGradeArray[$grade['primary_id']] = $grade;
                }
                $default_grade = [];
                foreach ($defaultData as $recMethod => $ids) {
                    if ($ids !== true &&
                        $recMethod !== 'other') {
                        foreach ($ids as $id) {
                            $default_grade[$recMethod][$id->default_grade_id] = [
                                // 'methodName' => $recMethod,
                                'method' => ReceivedMethod::getDescription($recMethod),
                                'grade_id' => $id->default_grade_id,
                                'grade_num' => $allGradeArray[$id->default_grade_id]['grade_num'],
                                'code' => $allGradeArray[$id->default_grade_id]['code'],
                                'name' => $allGradeArray[$id->default_grade_id]['name'],
                            ];
                        }
                    } else {
                        if ($recMethod == 'other') {
                            $default_grade[$recMethod] = $allGradeArray;
                        } else {
                            $default_grade[$recMethod] = [];
                        }
                    }
                }

                $currencyDefault = DB::table('acc_currency')
                    ->leftJoin('acc_received_default', 'acc_currency.received_default_fk', '=', 'acc_received_default.id')
                    ->select(
                        'acc_currency.name as currency_name',
                        'acc_currency.id as currency_id',
                        'acc_currency.rate',
                        'default_grade_id',
                        'acc_received_default.name as method_name'
                    )
                    ->orderBy('acc_currency.id')
                    ->get();
                $currency_default_grade = [];
                foreach ($currencyDefault as $default) {
                    $currency_default_grade[$default->default_grade_id][] = [
                        'currency_id' => $default->currency_id,
                        'currency_name' => $default->currency_name,
                        'rate' => $default->rate,
                        'default_grade_id' => $default->default_grade_id,
                    ];
                }
                // grade process end

                $cheque_status = ChequeStatus::get_key_value();

                return view('cms.commodity.purchase.ro_review', [
                    'breadcrumb_data' => [
                        'purchase_sn' => $purchaseData->purchase_sn,
                        'purchase_id' => $return->purchase_id,
                        'return_id' => $return->id,
                    ],
                    'form_action' => route('cms.purchase.ro-review', ['return_id' => request('return_id')]),
                    'received_order' => $received_order,
                    'return' => $return,
                    'return_item' => $return_item,
                    'received_data' => $received_data,
                    'undertaker' => $undertaker,
                    'debit' => $debit,
                    'credit' => $credit,
                    'card_type' => $card_type,
                    'checkout_area' => $checkout_area,
                    'cheque_status' => $cheque_status,
                    'credit_card_grade' => $default_grade[ReceivedMethod::CreditCard],
                    'cheque_grade' => $default_grade[ReceivedMethod::Cheque],
                    // 'default_grade'=>$default_grade,
                    // 'currency_default_grade'=>$currency_default_grade,
                ]);
            }
        }
    }

    public function ro_taxation(Request $request, $return_id)
    {
        $request->merge([
            'id' => $return_id,
        ]);
        $request->validate([
            'id' => 'required|exists:pcs_purchase_return,id',
        ]);

        $ro_collection = ReceivedOrder::where([
            'source_type' => app(PurchaseReturn::class)->getTable(),
            'source_id' => $return_id,
        ]);

        $ro_data = $ro_collection->get();
        if (count($ro_data) == 0 || !$ro_collection->first()->balance_date) {
            return abort(404);
        }

        $received_order = $ro_collection->first();

        if ($request->isMethod('post')) {
            $request->validate([
                'received' => 'required|array',
                'product' => 'required|array',
                'return_item' => 'required|array',
            ]);

            DB::beginTransaction();

            try {
                if (request('received') && is_array(request('received'))) {
                    $received = request('received');
                    foreach ($received as $key => $value) {
                        $value['received_id'] = $key;
                        ReceivedOrder::update_received($value);
                    }
                }

                if (request('product') && is_array(request('product'))) {
                    $product = request('product');
                    foreach ($product as $key => $value) {
                        $value['product_id'] = $key;
                        Product::update_product_taxation($value);
                    }
                }

                if (request('return_item') && is_array(request('return_item'))) {
                    $return_item = request('return_item');
                    foreach ($return_item as $key => $value) {
                        $value['return_item_id'] = $key;
                        PurchaseReturnItem::update_return_item($value);
                    }
                }

                DB::commit();
                wToast(__('摘要/稅別更新成功'));

            } catch (\Exception $e) {
                DB::rollback();
                wToast(__('摘要/稅別更新失敗'), ['type' => 'danger']);
            }

            return redirect()->route('cms.purchase.ro-receipt', ['return_id' => request('return_id')]);

        } else if ($request->isMethod('get')) {
            $return = PurchaseReturn::findOrFail($return_id);
            $return_item = PurchaseReturnItem::return_item_list($return_id, 1, null)->get();

            $purchaseData = Purchase::getPurchase($return->purchase_id)->first();
            if (!$purchaseData) {
                return abort(404);
            }

            $received_data = ReceivedOrder::get_received_detail($ro_data->pluck('id')->toArray());

            $total_grades = GeneralLedger::total_grade_list();

            return view('cms.commodity.purchase.ro_taxation', [
                'breadcrumb_data' => [
                    'purchase_sn' => $purchaseData->purchase_sn,
                    'purchase_id' => $return->purchase_id,
                    'return_id' => $return->id,
                ],
                'form_action' => route('cms.purchase.ro-taxation', ['return_id' => request('return_id')]),
                'received_order' => $received_order,
                'return' => $return,
                'return_item' => $return_item,
                'received_data' => $received_data,
                'total_grades' => $total_grades,
            ]);
        }
    }
}

