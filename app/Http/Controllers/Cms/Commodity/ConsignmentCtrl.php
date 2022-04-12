<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Consignment\AuditStatus;
use App\Enums\Delivery\Event;
use App\Enums\Purchase\InboundStatus;
use App\Http\Controllers\Controller;
use App\Models\Consignment;
use App\Models\ConsignmentItem;
use App\Models\Delivery;
use App\Models\Depot;
use App\Models\PurchaseInbound;
use App\Models\PurchaseLog;
use App\Models\ReceiveDepot;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConsignmentCtrl extends Controller
{

    public function index(Request $request)
    {
        $query = $request->query();
        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;

        $all_inbound_status = [];
        foreach (InboundStatus::asArray() as $data) {
            $all_inbound_status[$data] = InboundStatus::getDescription($data);
        }

        $consignment_sn = Arr::get($query, 'consignment_sn', '');
        $send_depot_id = Arr::get($query, 'send_depot_id', '');
        $receive_depot_id = Arr::get($query, 'receive_depot_id', '');
        $csn_sdate = Arr::get($query, 'csn_sdate', '');
        $csn_edate = Arr::get($query, 'csn_edate', '');
        $audit_status = Arr::get($query, 'audit_status', AuditStatus::unreviewed()->value);
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

        return view('cms.commodity.consignment.list', [
            'dataList' => $dataList
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
            'product_style_id.*' => 'required|numeric',
            'name.*' => 'required|string',
            'sku.*' => 'required|string',
            'price.*' => 'required|numeric',
            'num.*' => 'required|numeric',
        ]);
        $query = $request->query();

        $csnReq = $request->only('send_depot_id', 'receive_depot_id', 'scheduled_date');
        $csnItemReq = $request->only('product_style_id', 'name', 'sku', 'num', 'price', 'memo');
//        $purchasePayReq = $request->only('logistics_price', 'logistics_memo', 'invoice_num', 'invoice_date');

        $send_depot = Depot::where('id', $csnReq['send_depot_id'])->get()->first();
        $receive_depot = Depot::where('id', $csnReq['receive_depot_id'])->get()->first();

        $consignmentID = null;
        $result = null;
        $result = DB::transaction(function () use ($csnReq, $csnItemReq, $request, $send_depot, $receive_depot
        ) {
            $reCsn = Consignment::createData($send_depot->id, $send_depot->name, $receive_depot->id, $receive_depot->name
                , $request->user()->id, $request->user()->name
                , $csnReq['scheduled_date']);

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
                Event::consignment()->value
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
        $consignmentItemData = ConsignmentItem::getOriginInboundDataList($id)->get();
;
        if (!$consignmentData) {
            return abort(404);
        }

        return view('cms.commodity.consignment.edit', [
            'id' => $id,
            'query' => $query,
            'consignmentData' => $consignmentData,
            'consignmentItemData' => $consignmentItemData,
            'method' => 'edit',
            'formAction' => Route('cms.consignment.edit', ['id' => $id]),
            'breadcrumb_data' => ['id' => $id, 'sn' => $consignmentData->consignment_sn],
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'product_style_id.*' => 'required|numeric',
            'name.*' => 'required|string',
            'sku.*' => 'required|string',
            'price.*' => 'required|numeric',
            'num.*' => 'required|numeric',
        ]);
        $query = $request->query();

        $csnReq = $request->only('scheduled_date', 'audit_status');
        $csnItemReq = $request->only('item_id', 'product_style_id', 'name', 'sku', 'num', 'price');

        //判斷是否有出貨審核，有則不可新增刪除商品款式
//        $consignmentGet = Consignment::where('id', '=', $id)->get()->first();
        $consignmentData  = Consignment::getDeliveryData($id)->get()->first();
        if (null != $consignmentData && AuditStatus::unreviewed()->value != $consignmentData->audit_status) {
            throw ValidationException::withMessages(['item_error' => '已寄倉審核，無法再修改']);
        }
        if (null != $consignmentData->dlv_audit_date || null != $consignmentData->audit_date) {
            if (isset($request['del_item_id']) && null != $request['del_item_id']) {
                throw ValidationException::withMessages(['item_error' => '已審核，不可刪除商品款式']);
            }
            if (isset($csnItemReq['item_id'])) {
                throw ValidationException::withMessages(['item_error' => '已審核，不可新增修改商品款式']);
            }
        }

        $changeStr = '';
        $repcsCTPD = Consignment::checkToUpdateConsignmentData($id, $csnReq, $changeStr, $request->user()->id, $request->user()->name);
        $changeStr .= $repcsCTPD['error_msg'];

        //刪除現有款式
        if (isset($request['del_item_id']) && null != $request['del_item_id']) {
            $changeStr .= 'delete purchaseItem id:' . $request['del_item_id'];
            $del_item_id_arr = explode(",", $request['del_item_id']);
            $rePcsDI = ConsignmentItem::deleteItems($consignmentData->consignment_id, $del_item_id_arr, $request->user()->id, $request->user()->name);
            if ($rePcsDI['success'] == 0) {
                $changeStr = $rePcsDI['error_msg'];
                throw ValidationException::withMessages(['item_error' => $rePcsDI['error_msg']]);
            }
        }

        if (isset($csnItemReq['item_id'])) {
            $resultUpd = DB::transaction(function () use ($request, $csnItemReq, $consignmentData, $changeStr
            ) {
                foreach ($csnItemReq['item_id'] as $key => $val) {
                    $itemId = $csnItemReq['item_id'][$key];
                    //有值則做更新
                    //itemId = null 代表新資料
                    if (null != $itemId) {
                        $resultUpd = ConsignmentItem::checkToUpdateItemData($itemId, $csnItemReq, $key, $changeStr, $request->user()->id, $request->user()->name);
                        if ($resultUpd['success'] == 0) {
                            DB::rollBack();
                            $changeStr = $resultUpd['error_msg'];
                            return $changeStr;
                        }
                    } else {
                        $changeStr .= ' add item:' . $csnItemReq['name'][$key];

                        $resultUpd = ConsignmentItem::createData(
                            [
                                'consignment_id' => $consignmentData->consignment_id,
                                'product_style_id' => $csnItemReq['product_style_id'][$key],
                                'title' => $csnItemReq['name'][$key],
                                'sku' => $csnItemReq['sku'][$key],
                                'price' => $csnItemReq['price'][$key],
                                'num' => $csnItemReq['num'][$key],
                            ],
                            $request->user()->id, $request->user()->name
                        );
                        if ($resultUpd['success'] == 0) {
                            DB::rollBack();
                            $changeStr = $resultUpd['error_msg'];
                            return $changeStr;
                        }
                    }
                }
                return ['success' => 1, 'error_msg' => ""];
            });
            if ($resultUpd['success'] == 0) {
                throw ValidationException::withMessages(['item_error' => $resultUpd['error_msg']]);
            }
        }
        $changeStr = '';
        wToast(__('Edit finished.') . ' ' . $changeStr);
        return redirect(Route('cms.consignment.edit', [
            'id' => $id,
            'query' => $query
        ]));
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

        $inboundList = PurchaseInbound::getInboundList(['event' => Event::consignment()->value, 'purchase_id' => $id])
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
            'breadcrumb_data' => $purchaseData->consignment_sn,
        ]);
    }

    public function storeInbound(Request $request, $id)
    {
        $request->validate([
            'depot_id' => 'required|numeric',
            'event_item_id.*' => 'required|numeric',
            'product_style_id.*' => 'required|numeric',
            'inbound_date.*' => 'required|string',
            'inbound_num.*' => 'required|numeric|min:1',
            'error_num.*' => 'required|numeric|min:0',
            'status.*' => 'required|numeric|min:0',
            'expiry_date.*' => 'required|string',
            'origin_inbound_id.*' => 'required|numeric',
        ]);
        $depot_id = $request->input('depot_id');
        $inboundItemReq = $request->only('event_item_id', 'product_style_id', 'inbound_date', 'inbound_num', 'error_num', 'inbound_memo', 'status', 'expiry_date', 'inbound_memo', 'origin_inbound_id');

        if (isset($inboundItemReq['product_style_id'])) {
            $depot = Depot::where('id', '=', $depot_id)->get()->first();

            $result = DB::transaction(function () use ($inboundItemReq, $id, $depot_id, $depot, $request
            ) {
                foreach ($inboundItemReq['product_style_id'] as $key => $val) {

                    $re = PurchaseInbound::createInbound(
                        Event::consignment()->value,
                        $id,
                        $inboundItemReq['event_item_id'][$key], //存入 dlv_receive_depot.id
                        $inboundItemReq['product_style_id'][$key],
                        $inboundItemReq['expiry_date'][$key],
                        $inboundItemReq['inbound_date'][$key],
                        $inboundItemReq['inbound_num'][$key],
                        $depot_id,
                        $depot->name,
                        $request->user()->id,
                        $request->user()->name,
                        $inboundItemReq['inbound_memo'][$key],
                        $inboundItemReq['origin_inbound_id'][$key]
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

        return view('cms.commodity.consignment.log', [
            'id' => $id,
            'purchaseData' => $purchaseData,
            'purchaseLog' => $purchaseLog,
            'breadcrumb_data' => $purchaseData->consignment_sn,
        ]);
    }
}

