<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Delivery\Event;
use App\Http\Controllers\Controller;
use App\Models\Consignment;
use App\Models\ConsignmentItem;
use App\Models\Delivery;
use App\Models\Depot;
use App\Models\PurchaseInbound;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsignmentCtrl extends Controller
{

    public function index(Request $request)
    {
        //return view('cms.commodity.consignment.list', []);
        return redirect(Route('cms.consignment.create'));
    }

    public function create(Request $request)
    {
        return view('cms.commodity.consignment.edit', [
            'method' => 'create',
            'depotList' => Depot::all(),
            'formAction' => Route('cms.consignment.create'),
        ]);
    }


    public function store(Request $request)
    {
        $query = $request->query();
        $this->validInputValue($request);

        $csnReq = $request->only('send_depot_id', 'receive_depot_id', 'scheduled_date');
        $csnItemReq = $request->only('product_style_id', 'name', 'sku', 'num', 'price', 'memo');
//        $purchasePayReq = $request->only('logistics_price', 'logistics_memo', 'invoice_num', 'invoice_date');

        $send_depot = Depot::where('id', $csnReq['send_depot_id'])->get()->first();
        $receive_depot = Depot::where('id', $csnReq['receive_depot_id'])->get()->first();

        $reCsn = Consignment::createData($send_depot->id, $send_depot->name, $receive_depot->id, $receive_depot->name
            , $request->user()->id, $request->user()->name
            , $csnReq['scheduled_date']);

        $consignmentID = null;
        if (isset($reCsn['id'])) {
            $consignmentID = $reCsn['id'];
        }

        $result = null;
        $result = DB::transaction(function () use ($csnItemReq, $request, $consignmentID
        ) {
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
                            'memo' => $csnItemReq['memo'][$key],
                        ],
                        $request->user()->id, $request->user()->name
                    );
                    if ($reCsnIC['success'] == 0) {
                        DB::rollBack();
                        return $reCsnIC;
                    }
                }
            }
            return ['success' => 1, 'error_msg' => ""];
        });
        $csn = Consignment::where('id', $consignmentID)->get()->first();
        $reDelivery = Delivery::createData(
            Event::consignment()->value
            , $consignmentID
            , $csn->sn
        );
        if ($reDelivery['success'] == 0) {
            return $reDelivery;
        }

        if ($result['success'] == 0) {
            wToast($result['error_msg']);
        } else {
            wToast(__('Add finished.'));
        }

        return redirect(Route('cms.consignment.edit', [
            'id' => $consignmentID,
            'query' => $query
        ]));
    }

    //驗證資料
    private function validInputValue(Request $request)
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
    }

    public function edit(Request $request, $id)
    {
        $query = $request->query();
        $consignmentData  = Consignment::getData($id)->get()->first();
        $consignmentItemData = ConsignmentItem::where('consignment_id', $id)->get()->toArray();

        if (!$consignmentData) {
            return abort(404);
        }

        return view('cms.commodity.consignment.edit', [
            'id' => $id,
            'query' => $query,
            'consignmentData' => $consignmentData,
            'method' => 'edit',
            'formAction' => Route('cms.consignment.edit', ['id' => $id]),
            'breadcrumb_data' => ['id' => $id, 'sn' => $consignmentData->consignment_sn],
        ]);
    }

    //入庫結案
    public function close(Request $request, $id) {
        dd('close:'.$id);
    }

    public function inbound(Request $request, $id) {
        $purchaseData  = Consignment::getData($id)->get()->first();
        $purchaseItemList = ConsignmentItem::where('consignment_id', $id)
            ->select('*', 'id as item_id')
            ->selectRaw('( COALESCE(num, 0) - COALESCE(arrived_num, 0) ) as should_enter_num')
            ->get();

        $inboundList = PurchaseInbound::getInboundList(['event' => Event::consignment()->key, 'purchase_id' => $id])->get()->toArray();
        $inboundOverviewList = PurchaseInbound::getOverviewInboundList(Event::consignment()->key, $id)->get()->toArray();
        $purchaseItemList = DB::table('dlv_delivery as delivery')
            ->leftJoin('dlv_receive_depot as rcv_depot', 'rcv_depot.delivery_id', '=', 'delivery.id')
            ->select('*'
                , 'rcv_depot.id as rcv_deppot_id'
            )
            ->selectRaw('DATE_FORMAT(expiry_date,"%Y-%m-%d") as expiry_date')
            ->selectRaw('( COALESCE(rcv_depot.qty, 0) - COALESCE(rcv_depot.csn_arrived_qty, 0) ) as should_enter_num')
            ->where('delivery.event', Event::consignment()->value)
            ->where('delivery.event_id', $id)
            ->whereNotNull('rcv_depot.id');

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
            'purchase_item_id.*' => 'required|numeric',
            'product_style_id.*' => 'required|numeric',
            'inbound_date.*' => 'required|string',
            'inbound_num.*' => 'required|numeric|min:1',
            'error_num.*' => 'required|numeric|min:0',
            'status.*' => 'required|numeric|min:0',
            'expiry_date.*' => 'required|string',
            'origin_inbound_id.*' => 'required|numeric',
        ]);
        $depot_id = $request->input('depot_id');
        $inboundItemReq = $request->only('purchase_item_id', 'product_style_id', 'inbound_date', 'inbound_num', 'error_num', 'inbound_memo', 'status', 'expiry_date', 'inbound_memo', 'origin_inbound_id');

        if (isset($inboundItemReq['product_style_id'])) {
            $depot = Depot::where('id', '=', $depot_id)->get()->first();

            $result = DB::transaction(function () use ($inboundItemReq, $id, $depot_id, $depot, $request
            ) {
                foreach ($inboundItemReq['product_style_id'] as $key => $val) {

                    $re = PurchaseInbound::createInbound(
                        Event::consignment()->key,
                        $id,
                        $inboundItemReq['purchase_item_id'][$key], //存入 dlv_receive_depot.id
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
        dd('deleteInbound:'.$id);
    }
}

