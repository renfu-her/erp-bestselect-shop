<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Payable\ChequeStatus;
use App\Enums\Consignment\AuditStatus;
use App\Enums\Delivery\Event;
use App\Enums\Purchase\InboundStatus;
use App\Enums\Purchase\LogEventFeature;
use App\Enums\StockEvent;
use App\Enums\Supplier\Payment;

use App\Http\Controllers\Controller;
use App\Models\AccountPayable;
use App\Models\AllGrade;
use App\Models\Consignment;
use App\Models\ConsignmentItem;
use App\Models\Consum;
use App\Models\DayEnd;
use App\Models\Delivery;
use App\Models\Depot;
use App\Models\ProductStock;
use App\Models\ProductStyle;
use App\Models\PurchaseInbound;
use App\Models\PurchaseLog;
use App\Models\ReceiveDepot;
use App\Models\PayingOrder;
use App\Models\PayableDefault;
use App\Models\Supplier;
use App\Models\User;
use App\Models\GeneralLedger;
use App\Models\PayableAccount;
use App\Models\PayableCash;
use App\Models\PayableCheque;
use App\Models\PayableForeignCurrency;
use App\Models\PayableOther;
use App\Models\PayableRemit;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConsignmentCtrl extends Controller
{

    public function index(Request $request)
    {
        $query = $request->query();
        $data_per_page = Arr::get($query, 'data_per_page', 100);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 100;

        $all_inbound_status = [];
        foreach (InboundStatus::asArray() as $data) {
            $all_inbound_status[$data] = InboundStatus::getDescription($data);
        }

        $consignment_sn = Arr::get($query, 'consignment_sn', '');
        $send_depot_id = Arr::get($query, 'send_depot_id', '');
        $receive_depot_id = Arr::get($query, 'receive_depot_id', '');
        $csn_sdate = Arr::get($query, 'csn_sdate', '');
        $csn_edate = Arr::get($query, 'csn_edate', '');
        $audit_status = Arr::get($query, 'audit_status', null);
        $inbound_status = Arr::get($query, 'inbound_status', implode(',', array_keys($all_inbound_status)));

        $inbound_status_arr = [];
        if ('' != $inbound_status) {
            $inbound_status_arr = explode(',', $inbound_status);
        }
        $dataList = ConsignmentItem::getOriginInboundDataListWithCSN(
                $consignment_sn
                , $send_depot_id
                , $receive_depot_id
                , $csn_sdate
                , $csn_edate
                , $audit_status
                , $inbound_status_arr
            )
            ->paginate($data_per_page)->appends($query);

        $uniqueGroups = [];
        $uniqueDataList = [];
        foreach ($dataList as $datum) {
            $isInUniqueGroup = false;
            //check if in group
            foreach ($uniqueGroups as $uniqueGroup) {
                if (
                    $uniqueGroup['consignment_id'] == $datum->consignment_id &&
                    $uniqueGroup['dlv_id'] == $datum->dlv_id
                ) {
                    $isInUniqueGroup = true;
                }
            }
            if (!$isInUniqueGroup) {
                $uniqueGroups[] = [
                    'consignment_id' => $datum->consignment_id,
                    'dlv_id'         => $datum->dlv_id,
                ];
                $datum->subGroups = ConsignmentItem::getItemsByConsignmentIdAndDlvId($datum->consignment_id, $datum->dlv_id);
                $uniqueDataList[] = $datum;
            }
        }

        return view('cms.commodity.consignment.list', [
            'dataList' => $dataList
            , 'uniqueDataList' => $uniqueDataList
            , 'data_per_page' => $data_per_page
            , 'depotList' => Depot::all()

            , 'consignment_sn' => $consignment_sn
            , 'send_depot_id' => $send_depot_id
            , 'receive_depot_id' => $receive_depot_id
            , 'csn_sdate' => $csn_sdate
            , 'csn_edate' => $csn_edate
            , 'audit_status' => $audit_status
            , 'inbound_status' => $inbound_status
            , 'all_inbound_status' => $all_inbound_status
        ]);
    }

    public function create(Request $request)
    {
        return view('cms.commodity.consignment.create', [
            'method' => 'create',
            'depotList' => Depot::all(),
            'formAction' => Route('cms.consignment.create'),
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'send_depot_id' => 'required|numeric',
            'receive_depot_id' => 'required|numeric',
            'scheduled_date' => 'required|string',
            'order_memo' => 'present',
            'product_style_id.*' => 'required|numeric|distinct',
            'name.*' => 'required|string',
            'prd_type.*' => 'required|string',
            'sku.*' => 'required|string',
            'price.*' => 'required|numeric',
            'num.*' => 'required|numeric',
        ]);
        $query = $request->query();

        $csnReq = $request->only('send_depot_id', 'receive_depot_id', 'scheduled_date', 'order_memo');
        $csnItemReq = $request->only('product_style_id', 'name', 'prd_type', 'sku', 'num', 'price', 'memo');
//        $purchasePayReq = $request->only('logistics_price', 'logistics_memo', 'invoice_num', 'invoice_date');

        $send_depot = Depot::where('id', $csnReq['send_depot_id'])->get()->first();
        $receive_depot = Depot::where('id', $csnReq['receive_depot_id'])->get()->first();

        $consignmentID = null;
        $result = null;
        $result = DB::transaction(function () use ($csnReq, $csnItemReq, $request, $send_depot, $receive_depot
        ) {
            $reCsn = Consignment::createData($send_depot->id, $send_depot->name, $receive_depot->id, $receive_depot->name
                , $request->user()->id, $request->user()->name
                , $csnReq['scheduled_date'] , $csnReq['order_memo']);

            $consignmentID = null;
            if (isset($reCsn['id'])) {
                $consignmentID = $reCsn['id'];
            }

            if (isset($csnItemReq['product_style_id']) && isset($consignmentID)) {

                foreach ($csnItemReq['product_style_id'] as $key => $val) {
                    $reCsnIC = ConsignmentItem::createData(
                        [
                            'consignment_id' => $consignmentID,
                            'product_style_id' => $val,
                            'title' => $csnItemReq['name'][$key],
                            'prd_type' => $csnItemReq['prd_type'][$key],
                            'sku' => $csnItemReq['sku'][$key],
                            'price' => $csnItemReq['price'][$key],
                            'num' => $csnItemReq['num'][$key],
                            'temp_id' => $csnItemReq['temp_id'][$key] ?? null,
//                            'memo' => $csnItemReq['memo'][$key],
                        ],
                        $request->user()->id, $request->user()->name
                    );
                    if ($reCsnIC['success'] == 0) {
                        DB::rollBack();
                        return $reCsnIC;
                    }
                }
            }

            $csn = Consignment::where('id', $consignmentID)->get()->first();
            $reDelivery = Delivery::createData(
                $request->user()
                , Event::consignment()->value
                , $consignmentID
                , $csn->sn
            );
            if ($reDelivery['success'] == 0) {
                return $reDelivery;
            }
            return ['success' => 1, 'error_msg' => "", 'consignmentID' => $consignmentID];
        });

        if ($result['success'] == 0) {
            wToast($result['error_msg']);
        } else {
            wToast(__('Add finished.'));
            $consignmentID = $result['consignmentID'];
        }

        return redirect(Route('cms.consignment.edit', [
            'id' => $consignmentID,
            'query' => $query
        ]));
    }

    public function edit(Request $request, $id)
    {
        $query = $request->query();
        $consignmentData  = Consignment::getDeliveryData($id)->get()->first();
        $consignmentItemData = ConsignmentItem::getOriginInboundDataList($id)
            ->leftJoin(app(ProductStyle::class)->getTable() . ' as style', 'style.id', '=', 'items.product_style_id')
            ->addSelect('style.in_stock')
            ->get();

        if (!$consignmentData) {
            return abort(404);
        }

        $delivery = DB::table('dlv_delivery')
            ->where('dlv_delivery.event_id', '=', $consignmentData->consignment_id)
            ->where('dlv_delivery.event', '=', Event::consignment()->value)
            ->first();
        $rcv_depot = ReceiveDepot::getDataList(['delivery_id' => $consignmentData->dlv_id])->get();
        $consumeItems = Consum::getConsumWithEvent(Event::consignment()->value, $id)->get()->toArray();

        return view('cms.commodity.consignment.edit', [
            'id' => $id,
            'query' => $query,
            'consignmentData' => $consignmentData,
            'consignmentItemData' => $consignmentItemData,
            'delivery' => $delivery,
            'consume_items' => $consumeItems,
            'rcv_depot' => $rcv_depot,
            'method' => 'edit',
            'formAction' => Route('cms.consignment.edit', ['id' => $id]),
            'breadcrumb_data' => ['id' => $id, 'sn' => $consignmentData->consignment_sn],
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'order_memo' => 'present',
            'product_style_id.*' => 'required|numeric|distinct',
            'name.*' => 'required|string',
            'sku.*' => 'required|string',
            'price.*' => 'required|numeric',
            'num.*' => 'required|numeric',
        ]);
        $query = $request->query();

        $csnReq = $request->only('scheduled_date', 'audit_status', 'order_memo');
        $csnItemReq = $request->only('item_id', 'product_style_id', 'name', 'prd_type', 'sku', 'num', 'price');

        //判斷是否有出貨審核，有則不可新增刪除商品款式
//        $consignmentGet = Consignment::where('id', '=', $id)->get()->first();
        $consignmentData  = Consignment::getDeliveryData($id)->get()->first();
        $rcv_depot = ReceiveDepot::getDataList(['delivery_id' => $consignmentData->dlv_id])->get();

        $editable = $consignmentData->close_date == null
            && ($consignmentData->audit_status == AuditStatus::unreviewed()->value
                || ($consignmentData->audit_status == AuditStatus::approved()->value && 0 == count($rcv_depot) )
            );

        if (false == $editable) {
            throw ValidationException::withMessages(['item_error' => '已寄倉審核，無法再修改']);
        }
        if ((null != $consignmentData->dlv_audit_date || null != $consignmentData->audit_date) && 0 < count($rcv_depot)) {
            if (isset($request['del_item_id']) && null != $request['del_item_id']) {
                throw ValidationException::withMessages(['item_error' => '已審核，不可刪除商品款式']);
            }
            if (isset($csnItemReq['item_id'])) {
                throw ValidationException::withMessages(['item_error' => '已審核，不可新增修改商品款式']);
            }
        }

        $msg = DB::transaction(function () use ($request, $id, $csnReq, $csnItemReq, $consignmentData
        ) {
            $repcsCTPD = Consignment::checkToUpdateConsignmentData($id, $csnReq, $request->user()->id, $request->user()->name);
            if ($repcsCTPD['success'] == 0) {
                DB::rollBack();
                return $repcsCTPD;
            }

            //刪除現有款式
            if (isset($request['del_item_id']) && null != $request['del_item_id']) {
                $del_item_id_arr = explode(",", $request['del_item_id']);
                $rePcsDI = ConsignmentItem::deleteItems($consignmentData->consignment_id, $del_item_id_arr, $request->user()->id, $request->user()->name);
                if ($rePcsDI['success'] == 0) {
                    DB::rollBack();
                    return $rePcsDI;
                }
            }

            if (isset($csnItemReq['item_id'])) {
                foreach ($csnItemReq['item_id'] as $key => $val) {
                    $itemId = $csnItemReq['item_id'][$key];
                    //有值則做更新
                    //itemId = null 代表新資料
                    if (null != $itemId) {
                        $resultUpd = ConsignmentItem::checkToUpdateItemData($itemId
                            , ['num' => $csnItemReq['num']]
                            , $key, $request->user()->id, $request->user()->name);
                        if ($resultUpd['success'] == 0) {
                            DB::rollBack();
                            return $resultUpd;
                        }
                    } else {
                        $resultUpd = ConsignmentItem::createData(
                            [
                                'consignment_id' => $consignmentData->consignment_id,
                                'product_style_id' => $csnItemReq['product_style_id'][$key],
                                'title' => $csnItemReq['name'][$key],
                                'prd_type' => $csnItemReq['prd_type'][$key],
                                'sku' => $csnItemReq['sku'][$key],
                                'price' => $csnItemReq['price'][$key],
                                'num' => $csnItemReq['num'][$key],
                            ],
                            $request->user()->id, $request->user()->name
                        );
                        if ($resultUpd['success'] == 0) {
                            DB::rollBack();
                            return $resultUpd;
                        }
                    }
                }
            }

            // 判斷出貨倉是理貨倉
            if (1 == $consignmentData->send_can_tally) {
                $queryCsnItems = DB::table('csn_consignment as csn')
                    ->leftJoin('csn_consignment_items as csn_items', 'csn_items.consignment_id', 'csn.id')
                    ->where('csn.id', $id)
                    ->get();
                $stock_event = StockEvent::consignment()->value;
                $user_name = $request->user()->name;

                //判斷audit_status變成核可，則須扣除數量
                if(AuditStatus::approved()->value != $consignmentData->audit_status && AuditStatus::approved()->value == $csnReq['audit_status']){
                    $stock_note = LogEventFeature::getDescription(LogEventFeature::delivery()->value);
                    foreach($queryCsnItems as $item) {
                        $rePSSC = ProductStock::stockChange($item->product_style_id, $item->num * -1
                            , $stock_event, $id
                            , $user_name . $stock_note
                            , false, $consignmentData->send_can_tally);
                        if ($rePSSC['success'] == 0) {
                            DB::rollBack();
                            return $rePSSC;
                        }
                    }
                }
                //判斷audit_status從核可變成其他狀態，則須加回數量
                else if (AuditStatus::approved()->value == $consignmentData->audit_status && AuditStatus::approved()->value != $csnReq['audit_status']) {
                    $stock_note = LogEventFeature::getDescription(LogEventFeature::delivery_cancle()->value);
                    foreach($queryCsnItems as $item) {
                        $rePSSC = ProductStock::stockChange($item->product_style_id, $item->num
                            , $stock_event, $id
                            , $user_name . $stock_note
                            , false, $consignmentData->send_can_tally);
                        if ($rePSSC['success'] == 0) {
                            DB::rollBack();
                            return $rePSSC;
                        }
                    }
                }
            }
            return ['success' => 1, 'error_msg' => 'all ok'];
        });
        if ($msg['success'] == 0) {
            throw ValidationException::withMessages(['item_error' => $msg['error_msg']]);
        }

        wToast(__('Edit finished.'));
        return redirect(Route('cms.consignment.edit', [
            'id' => $id,
            'query' => $query
        ]));
    }

    // 列印－出貨單明細
    public function print_order_ship(Request $request, $id)
    {
        $query = $request->query();
        $ptype = empty($query['type']) ? 'M1': $query['type'];
        $consignmentData  = Consignment::getDeliveryData($id)->get()->first();
        $consignmentItemData = ConsignmentItem::getOriginInboundDataList($id)->get();

        if (!$consignmentData) {
            return abort(404);
        }

        return view('doc.print_csn_order', [
            'type' => 'ship',
            'ptype' => $ptype,
            'id' => $id,
            'user' => $request->user(),
            'consignmentData' => $consignmentData,
            'consignmentItemData' => $consignmentItemData,
        ]);
    }

    public function delete(Request $request, $id)
    {
        $result = Consignment::del($id, $request->user()->id, $request->user()->name);
        if ($result['success'] == 0) {
            wToast($result['error_msg']);
        } else {
            wToast(__('Delete finished.'));
        }
        return redirect(Route('cms.consignment.index'));
    }

    //入庫結案
    public function close(Request $request, $id) {
        $inboundOverviewList = PurchaseInbound::getOverviewInboundList(Event::consignment()->value, $id)->get()->toArray();
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
            Consignment::close($id, $request->user()->id, $request->user()->name);
        }

        wToast(__('Close finished.'));
        return redirect(Route('cms.consignment.inbound', [
            'id' => $id,
        ]));
    }

    public function inbound(Request $request, $id) {
        $purchaseData  = Consignment::getData($id)->get()->first();
        $purchaseItemList = ReceiveDepot::getShouldEnterNumDataList(Event::consignment()->value, $id);

        $inboundList = PurchaseInbound::getInboundList(['event' => Event::consignment()->value, 'event_id' => $id])
            ->orderByDesc('inbound.created_at')
            ->get()->toArray();
        $inboundOverviewList = PurchaseInbound::getOverviewInboundList(Event::consignment()->value, $id)->get()->toArray();


        $depotList = Depot::all()->toArray();
        return view('cms.commodity.consignment.inbound', [
            'purchaseData' => $purchaseData,
            'id' => $id,
            'send_depot_id' => $purchaseData->send_depot_id,
            'purchaseItemList' => $purchaseItemList->get(),
            'inboundList' => $inboundList,
            'inboundOverviewList' => $inboundOverviewList,
            'depotList' => $depotList,
            'formAction' => Route('cms.consignment.store_inbound', ['id' => $id,]),
            'formActionClose' => Route('cms.consignment.close', ['id' => $id,]),
            'breadcrumb_data' => ['id' => $id, 'sn' => $purchaseData->consignment_sn],
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
            'expiry_date.*' => 'present',
            'prd_type.*' => 'required|string',
        ]);
        $depot_id = $request->input('depot_id');
        $inboundItemReq = $request->only('event_item_id', 'product_style_id', 'inbound_date', 'inbound_num', 'error_num', 'inbound_memo', 'status', 'expiry_date', 'inbound_memo', 'prd_type');

        if (isset($inboundItemReq['product_style_id'])) {
            //檢查若輸入實進數量小於0，打負數時備註欄位要必填說明原因
            foreach ($inboundItemReq['product_style_id'] as $key => $val) {
                if (1 > $inboundItemReq['inbound_num'][$key] && true == empty($inboundItemReq['inbound_memo'][$key])) {
                    throw ValidationException::withMessages(['inbound_memo.'.$key => '打負數時備註欄位要必填說明原因']);
                }
            }

            $depot = Depot::where('id', '=', $depot_id)->get()->first();
            $style_arr = PurchaseInbound::getCreateData(Event::consignment()->value, $id, $inboundItemReq['event_item_id'], $inboundItemReq['product_style_id']);

            $result = DB::transaction(function () use ($inboundItemReq, $id, $depot_id, $depot, $request, $style_arr
            ) {
                $consignment = Consignment::where('id', '=', $id)->first();
                if (false == isset($consignment)) {
                    DB::rollBack();
                    return ['success' => 0, 'error_msg' => "無此寄倉單 不可入庫"];
                }
                foreach ($style_arr as $key => $val) {
                    $re = PurchaseInbound::createInbound(
                        Event::consignment()->value,
                        $id,
                        $inboundItemReq['event_item_id'][$key], //存入 dlv_receive_depot.id
                        $inboundItemReq['product_style_id'][$key],
                        $val['item']['title'] . '-'. $val['item']['spec'],
                        $val['sku'],
                        $val['unit_cost'],
                        $inboundItemReq['expiry_date'][$key] ?? null,
                        $inboundItemReq['inbound_date'][$key],
                        $inboundItemReq['inbound_num'][$key],
                        $depot_id,
                        $depot->name,
                        $request->user()->id,
                        $request->user()->name,
                        $inboundItemReq['inbound_memo'][$key],
                        $inboundItemReq['prd_type'][$key],
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
        return redirect(Route('cms.consignment.inbound', [
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
        return redirect(Route('cms.consignment.inbound', [
            'id' => $purchase_id,
        ]));
    }

    /**
     * 變更歷史
     */
    public function historyLog(Request $request, $id) {
        $purchaseData = Consignment::getData($id)->first();
        $purchaseLog = PurchaseLog::getData(Event::consignment()->value, $id)->get();
        if (!$purchaseData) {
            return abort(404);
        }

        return view('cms.commodity.purchase.log', [
            'id' => $id,
            'purchaseData' => $purchaseData,
            'purchaseLog' => $purchaseLog,
            'returnAction' => Route('cms.consignment.index', [], true),
            'title' => '寄倉單',
            'sn' => $purchaseData->consignment_sn,
            'event' => Event::consignment()->value,
            'breadcrumb_data' => $purchaseData->consignment_sn,
        ]);
    }


    public function logistic_po(Request $request, $id)
    {
        $request->merge([
            'id' => $id,
        ]);

        $request->validate([
            'id' => 'required|exists:csn_consignment,id',
        ]);

        $source_type = app(Consignment::class)->getTable();
        $type = 1;

        $paying_order = PayingOrder::where([
            'source_type' => $source_type,
            'source_id' => $id,
            'source_sub_id' => null,
            'type' => $type,
            'deleted_at' => null,
        ])->first();

        $consignmentData  = Consignment::getDeliveryData($id)->get()->first();
        $supplier = Supplier::find($consignmentData->supplier_id);

        if (!$paying_order) {
            $price = $consignmentData->lgt_cost;
            $product_grade = PayableDefault::where('name', '=', 'product')->first()->default_grade_id;
            $logistics_grade = PayableDefault::where('name', '=', 'logistics')->first()->default_grade_id;

            $result = PayingOrder::createPayingOrder(
                $source_type,
                $id,
                null,
                $request->user()->id,
                $type,
                $product_grade,
                $logistics_grade,
                $price ?? 0,
                '',
                '',
                $supplier ? $supplier->id : null,
                $supplier ? ($supplier->nickname ? $supplier->name . ' - ' . $supplier->nickname : $supplier->name) : null,
                $supplier ? $supplier->contact_tel : null,
                $supplier ? $supplier->contact_address : null
            );

            $paying_order = PayingOrder::findOrFail($result['id']);
        }

        $applied_company = DB::table('acc_company')->where('id', 1)->first();

        $logistics_grade_name = AllGrade::find($paying_order->logistics_grade_id)->eachGrade->code . ' ' . AllGrade::find($paying_order->logistics_grade_id)->eachGrade->name;

        if ($consignmentData->projlgt_order_sn) {
            $logistics_grade_name = $logistics_grade_name . ' ' . $consignmentData->group_name . ' #'. $consignmentData->projlgt_order_sn;
        } else {
            $logistics_grade_name = $logistics_grade_name . ' ' . $consignmentData->group_name . ' #'. $consignmentData->package_sn;
        }

        $payable_data = PayingOrder::get_payable_detail($paying_order->id);
        $data_status_check = PayingOrder::payable_data_status_check($payable_data);

        $accountant = User::whereIn('id', $payable_data->pluck('accountant_id_fk')->toArray())->get();
        $accountant = array_unique($accountant->pluck('name')->toArray());
        asort($accountant);

        $undertaker = User::find($paying_order->usr_users_id);

        $zh_price = num_to_str($paying_order->price);

        if($paying_order && $paying_order->append_po_id){
            $append_po = PayingOrder::find($paying_order->append_po_id);
            $paying_order->append_po_link = PayingOrder::paying_order_link($append_po->source_type, $append_po->source_id, $append_po->source_sub_id, $append_po->type);
        }

        $view = 'cms.commodity.consignment.logistic_po';
        if (request('action') == 'print') {
            $view = 'doc.print_consignment_logistic_pay';
        }

        return view($view, [
            'breadcrumb_data' => ['id' => $id, 'sn' => $consignmentData->consignment_sn],

            'paying_order' => $paying_order,
            'payable_data' => $payable_data,
            'data_status_check' => $data_status_check,
            'consignmentData' => $consignmentData,
            'undertaker' => $undertaker,
            'applied_company' => $applied_company,
            'logistics_grade_name' => $logistics_grade_name,
            'accountant' => implode(',', $accountant),
            'zh_price' => $zh_price,
        ]);
    }

    public function logistic_po_create(Request $request, $id)
    {
        $request->merge([
            'id' => $id,
        ]);

        $request->validate([
            'id' => 'required|exists:csn_consignment,id',
        ]);

        $source_type = app(Consignment::class)->getTable();
        $type = 1;

        $paying_order = PayingOrder::where([
            'source_type' => $source_type,
            'source_id' => $id,
            'source_sub_id' => null,
            'type' => $type,
            'deleted_at' => null,
        ])->first();

        if (!$paying_order) {
            return abort(404);
        }

        if ($request->isMethod('post')) {
            $request->merge([
                'pay_order_id' => $paying_order->id,
            ]);

            $request->validate([
                'acc_transact_type_fk' => 'required|regex:/^[1-6]$/',
            ]);

            $req = $request->all();

            $payable_type = $req['acc_transact_type_fk'];

            switch ($payable_type) {
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

            $payable_data = PayingOrder::get_payable_detail($paying_order->id);
            if (count($payable_data) > 0 && $paying_order->price == $payable_data->sum('tw_price')) {
                $paying_order->update([
                    'balance_date' => date('Y-m-d H:i:s'),
                    'payment_date' => $req['payment_date'],
                ]);

                DayEnd::match_day_end_status($req['payment_date'], $paying_order->sn);
            }

            if (PayingOrder::find($paying_order->id) && PayingOrder::find($paying_order->id)->balance_date) {
                return redirect()->route('cms.consignment.logistic-po', [
                    'id' => $id,
                ]);

            } else {
                return redirect()->route('cms.consignment.logistic-po-create', [
                    'id' => $id,
                ]);
            }

        } else {

            if ($paying_order->balance_date) {
                return abort(404);
            }

            $consignmentData  = Consignment::getDeliveryData($id)->get()->first();
            $supplier = Supplier::find($consignmentData->supplier_id);

            $logistics_grade_name = AllGrade::find($paying_order->logistics_grade_id)->eachGrade->code . ' ' . AllGrade::find($paying_order->logistics_grade_id)->eachGrade->name;

            $currency = DB::table('acc_currency')->find($paying_order->acc_currency_fk);
            if (!$currency) {
                $currency = (object) [
                    'name' => 'NTD',
                    'rate' => 1,
                ];
            }

            $payable_data = PayingOrder::get_payable_detail($paying_order->id);

            $tw_price = $paying_order->price - $payable_data->sum('tw_price');

            $total_grades = GeneralLedger::total_grade_list();

            return view('cms.commodity.consignment.logistic_po_create', [
                'breadcrumb_data' => ['id' => $id, 'sn' => $consignmentData->consignment_sn],
                'paying_order' => $paying_order,
                'payable_data' => $payable_data,
                'consignmentData' => $consignmentData,
                'supplier' => $supplier,
                'logistics_grade_name' => $logistics_grade_name,
                'currency' => $currency,
                'tw_price' => $tw_price,
                'total_grades' => $total_grades,

                'cashDefault' => PayableDefault::where('name', 'cash')->pluck('default_grade_id')->toArray(),
                'chequeDefault' => PayableDefault::where('name', 'cheque')->pluck('default_grade_id')->toArray(),
                'remitDefault' => PayableDefault::where('name', 'remittance')->pluck('default_grade_id')->toArray(),
                'all_currency' => PayableDefault::getCurrencyOptionData()['selectedCurrencyResult']->toArray(),
                'currencyDefault' => PayableDefault::where('name', 'foreign_currency')->pluck('default_grade_id')->toArray(),
                'accountPayableDefault' => PayableDefault::where('name', 'accounts_payable')->pluck('default_grade_id')->toArray(),
                'otherDefault' => PayableDefault::where('name', 'other')->pluck('default_grade_id')->toArray(),

                'form_action' => Route('cms.consignment.logistic-po-create', ['id' => $id]),
                'method' => 'create',
                'transactTypeList' => AccountPayable::getTransactTypeList(),
                'chequeStatus' => ChequeStatus::get_key_value(),
            ]);
        }
    }
}

