<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Delivery\Event;
use App\Http\Controllers\Controller;
use App\Models\Consum;
use App\Models\CsnOrder;
use App\Models\CsnOrderItem;
use App\Models\Delivery;
use App\Models\Depot;
use App\Models\PurchaseLog;
use App\Models\ReceiveDepot;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConsignmentOrderCtrl extends Controller
{
    //寄倉訂購列表
    public function index(Request $request) {
        $query = $request->query();
        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;

        $depot_id = Arr::get($query, 'depot_id', 1);

        $queryCsnOrd = DB::table('csn_orders as csnord')
            ->leftJoin('csn_order_items as items', 'items.csnord_id', '=', 'csnord.id')
            ->leftJoin('dlv_delivery as delivery', function ($join) {
                $join->on('delivery.event_id', '=', 'csnord.id')
                    ->where('delivery.event', '=', Event::csn_order()->value);
            })
            ->whereNull('csnord.deleted_at')
            ->select('csnord.id'
                , 'csnord.sn'
                , 'csnord.depot_id'
                , 'csnord.depot_name'
                , 'csnord.create_user_name'
                , DB::raw('DATE_FORMAT(csnord.scheduled_date,"%Y-%m-%d") as scheduled_date')
                , 'csnord.memo'
                , 'csnord.created_at'

                , 'items.product_style_id'
                , 'items.prd_type'
                , 'items.title'
                , 'items.sku'
                , DB::raw('round(items.price) as price')
                , 'items.num'
                , DB::raw('round(items.price * items.num, 0) as total_price')
                , 'items.memo as item_memo'
                , 'delivery.logistic_status as logistic_status'
                , DB::raw('DATE_FORMAT(delivery.audit_date,"%Y-%m-%d") as audit_date')
            );

        if ($depot_id) {
            $queryCsnOrd->where('csnord.depot_id', $depot_id);
        }
        $dataList = $queryCsnOrd->paginate($data_per_page)->appends($query);

        return view('cms.commodity.consignment_order.index', [
            'dataList' => $dataList
            , 'data_per_page' => $data_per_page
            , 'depotList' => Depot::all()
            , 'depot_id' => $depot_id
        ]);
    }

    //寄倉訂購
    public function create(Request $request) {
        return view('cms.commodity.consignment_order.create', [
            'method' => 'create',
            'depotList' => Depot::all(),
            'formAction' => Route('cms.consignment-order.create'),
        ]);
    }

    //寄倉訂購
    public function store(Request $request) {

        $request->validate([
            'depot_id' => 'required|numeric',
            'scheduled_date' => 'required|string',
            'product_style_id.*' => 'required|numeric|distinct',
            'name.*' => 'required|string',
            'prd_type.*' => 'required|string',
            'sku.*' => 'required|string',
            'price.*' => 'required|numeric',
            'num.*' => 'required|numeric|min:1',
        ]);
        $query = $request->query();

//        dd(111, $request->all());
        $csnReq = $request->only('depot_id', 'scheduled_date');
        $csnItemReq = $request->only('product_style_id', 'name', 'prd_type', 'sku', 'num', 'price', 'memo');

        $depot = Depot::where('id', $csnReq['depot_id'])->get()->first();

        $consignmentID = null;
        $result = null;
        $result = DB::transaction(function () use ($csnReq, $csnItemReq, $request, $depot
        ) {
            $reCsn = CsnOrder::createData($depot->id, $depot->name
                , $request->user()->id, $request->user()->name
                , $csnReq['scheduled_date']);

            $consignmentID = null;
            if (isset($reCsn['id'])) {
                $consignmentID = $reCsn['id'];
            }

            if (isset($csnItemReq['product_style_id']) && isset($consignmentID)) {

                foreach ($csnItemReq['product_style_id'] as $key => $val) {
                    $reCsnIC = CsnOrderItem::createData(
                        [
                            'csnord_id' => $consignmentID,
                            'product_style_id' => $val,
                            'prd_type' => $csnItemReq['prd_type'][$key],
                            'title' => $csnItemReq['name'][$key],
                            'sku' => $csnItemReq['sku'][$key],
                            'price' => $csnItemReq['price'][$key],
                            'num' => $csnItemReq['num'][$key],
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

            $csn = CsnOrder::where('id', $consignmentID)->get()->first();
            $reDelivery = Delivery::createData(
                Event::csn_order()->value
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

        return redirect(Route('cms.consignment-order.edit', [
            'id' => $consignmentID,
            'query' => $query
        ]));
    }

    public function edit(Request $request, $id)
    {
        $query = $request->query();
        $consignmentData  = CsnOrder::getData($id)->get()->first();
        $consignmentItemData = CsnOrderItem::getData($id)->get();

        $delivery = Delivery::where('event', Event::csn_order()->value)
            ->where('event_id', $id)
            ->get()->first();
        if (!$consignmentData) {
            return abort(404);
        }
        $consumeItems = Consum::getConsumWithEvent(Event::csn_order()->value, $id)->get()->toArray();

        return view('cms.commodity.consignment_order.create', [
            'id' => $id,
            'query' => $query,
            'consume_items' => $consumeItems,
            'method' => 'edit',
            'depotList' => Depot::all(),
            'formAction' => Route('cms.consignment-order.index'),

            'consignmentData' => $consignmentData,
            'consignmentItemData' => $consignmentItemData,
            'delivery' => $delivery,
            'method' => 'edit',
            'formAction' => Route('cms.consignment-order.edit', ['id' => $id]),
            'breadcrumb_data' => ['id' => $id, 'sn' => $consignmentData->sn],
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'depot_id' => 'required|numeric',
            'scheduled_date' => 'required|string',
            'product_style_id.*' => 'required|numeric|distinct',
            'name.*' => 'required|string',
            'prd_type.*' => 'required|string',
            'sku.*' => 'required|string',
            'price.*' => 'required|numeric',
            'num.*' => 'required|numeric|min:1',
        ]);
        $query = $request->query();

//        dd(111, $request->all());
        $csnReq = $request->only('scheduled_date');
        $csnItemReq = $request->only('item_id', 'product_style_id', 'name', 'prd_type', 'sku', 'num', 'price', 'memo');

        //判斷是否有出貨審核，有則不可新增刪除商品款式
        $consignmentData  = CsnOrder::getData($id)->get()->first();
        $delivery  = Delivery::getData(Event::csn_order()->value, $id)->get()->first();
        $receiveDepot = ReceiveDepot::getDataList(['delivery_id' => $delivery->id])->get()->toArray();
        if (null != $consignmentData && null != $consignmentData->close_date) {
            throw ValidationException::withMessages(['item_error' => '已結案，無法再修改']);
        }
        if (null != $delivery->audit_date || 0 < count($receiveDepot)) {
            if (isset($request['del_item_id']) && null != $request['del_item_id']) {
                throw ValidationException::withMessages(['item_error' => '已出貨，不可刪除商品款式']);
            }
            if (isset($csnItemReq['item_id'])) {
                throw ValidationException::withMessages(['item_error' => '已出貨，不可新增修改商品款式']);
            }
        }

        $msg = DB::transaction(function () use ($request, $id, $csnReq, $csnItemReq, $consignmentData
        ) {
            $repcsCTPD = CsnOrder::checkToUpdateConsignmentData($id, $csnReq, $request->user()->id, $request->user()->name);
            if ($repcsCTPD['success'] == 0) {
                DB::rollBack();
                return $repcsCTPD;
            }

            //刪除現有款式
            if (isset($request['del_item_id']) && null != $request['del_item_id']) {
                //dd(222, $request['del_item_id']);
                $del_item_id_arr = explode(",", $request['del_item_id']);
                $rePcsDI = CsnOrderItem::deleteItems($consignmentData->id, $del_item_id_arr, $request->user()->id, $request->user()->name);
                if ($rePcsDI['success'] == 0) {
                    DB::rollBack();
                    return $rePcsDI;
                }
            }
//            dd(999999);
            if (isset($csnItemReq['item_id'])) {
                foreach ($csnItemReq['item_id'] as $key => $val) {
                    $itemId = $csnItemReq['item_id'][$key];
                    //有值則做更新
                    //itemId = null 代表新資料
                    if (null != $itemId) {
                        $resultUpd = CsnOrderItem::checkToUpdateItemData($itemId
                            , ['num' => $csnItemReq['num'], 'memo' => $csnItemReq['memo']]
                            , $key, $request->user()->id, $request->user()->name);
                        if ($resultUpd['success'] == 0) {
                            DB::rollBack();
                            return $resultUpd;
                        }
                    } else {
                        $resultUpd = CsnOrderItem::createData(
                            [
                                'csnord_id' => $id,
                                'product_style_id' => $csnItemReq['product_style_id'][$key],
                                'prd_type' => $csnItemReq['prd_type'][$key],
                                'title' => $csnItemReq['name'][$key],
                                'sku' => $csnItemReq['sku'][$key],
                                'price' => $csnItemReq['price'][$key],
                                'num' => $csnItemReq['num'][$key],
                                'memo' => $csnItemReq['memo'][$key],
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

            return ['success' => 1, 'error_msg' => 'all ok'];
        });

        if ($msg['success'] == 0) {
            throw ValidationException::withMessages(['item_error' => $msg['error_msg']]);
        }

        wToast(__('Edit finished.'));
        return redirect(Route('cms.consignment-order.edit', [
            'id' => $id,
            'query' => $query
        ]));
    }

    public function historyLog(Request $request, $id) {
        $purchaseData = CsnOrder::getData($id)->first();
        $purchaseLog = PurchaseLog::getData(Event::csn_order()->value, $id)->get();
        if (!$purchaseData) {
            return abort(404);
        }

        return view('cms.commodity.purchase.log', [
            'id' => $id,
            'purchaseData' => $purchaseData,
            'purchaseLog' => $purchaseLog,
            'returnAction' => Route('cms.consignment-order.edit', ['id' => $id], true),
            'title' => '寄倉訂購單',
            'sn' => $purchaseData->sn,
            'event' => Event::csn_order()->value,
            'breadcrumb_data' => $purchaseData->sn,
        ]);
    }
}

