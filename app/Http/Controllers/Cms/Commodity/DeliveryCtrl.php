<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Delivery\Event;
use App\Enums\Delivery\LogisticStatus;
use App\Enums\Purchase\LogEventFeature;
use App\Enums\StockEvent;
use App\Http\Controllers\Controller;
use App\Models\Consignment;
use App\Models\CsnOrder;
use App\Models\Delivery;
use App\Models\Depot;
use App\Models\DlvBack;
use App\Models\OrderItem;
use App\Models\ProductStock;
use App\Models\PurchaseInbound;
use App\Models\PurchaseLog;
use App\Models\ReceiveDepot;
use App\Models\ShipmentCategory;
use App\Models\SubOrders;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();
        $cond = [];
        $cond['delivery_sn'] = Arr::get($query, 'delivery_sn', null);
        $cond['event_sn'] = Arr::get($query, 'event_sn', null);
        $cond['receive_depot_id'] = Arr::get($query, 'receive_depot_id', []);
        $cond['ship_method'] = Arr::get($query, 'ship_method', []);
        $cond['logistic_status_code'] = Arr::get($query, 'logistic_status_code', []);
        $cond['ship_category'] = Arr::get($query, 'ship_category', []);

        $cond['order_sdate'] = Arr::get($query, 'order_sdate', null);
        $cond['order_edate'] = Arr::get($query, 'order_edate', null);
        $cond['delivery_sdate'] = Arr::get($query, 'delivery_sdate', null);
        $cond['delivery_edate'] = Arr::get($query, 'delivery_edate', null);

        $cond['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page', 10));

        $delivery = null;
        if (false == empty($cond['ship_category'])) {
            $ship_method = $cond['ship_method'];
            $ship_category = $cond['ship_category'];
            if ('pickup' == $ship_category) {
                $cond['ship_method'] = [];
            }
            $delivery = Delivery::getList($cond)->paginate($cond['data_per_page'])->appends($query);
            $cond['ship_method'] = $ship_method;
            $cond['ship_category'] = $ship_category;
        } else {
            $delivery = Delivery::getList($cond)->paginate($cond['data_per_page'])->appends($query);
        }

        return view('cms.commodity.delivery.list', [
            'dataList' => $delivery,
            'depotList' => Depot::all(),
            'shipmentCategory' => ShipmentCategory::all(),
            'logisticStatus' => LogisticStatus::asArray(),
            'searchParam' => $cond,
            'data_per_page' => $cond['data_per_page']]);
    }

    public function create($event, $eventId)
    {
        $rsp_arr = [
            'event' => $event,
            'eventId' => $eventId,
        ];
        $delivery = null;
        $delivery_id = null;
        $event_sn = '';
        if(Event::order()->value == $event) {
            $sub_order = SubOrders::getListWithShiGroupById($eventId)->get()->first();
            $event_sn = $sub_order->sn;
            if (null == $sub_order) {
                return abort(404);
            }
            $rsp_arr['order_id'] = $sub_order->order_id;

            // 出貨單號ID
            $delivery = Delivery::getData($event, $sub_order->id)->get()->first();
            $delivery_id = $delivery->id;
            $ord_items_arr = ReceiveDepot::getOrderShipItemWithDeliveryWithReceiveDepotList($event, $eventId, $delivery_id);
        } else if(Event::consignment()->value == $event) {
            // 出貨單號ID
            $delivery = Delivery::getData($event, $eventId)->get()->first();
            $delivery_id = $delivery->id;
            $ord_items_arr = ReceiveDepot::getCSNShipItemWithDeliveryWithReceiveDepotList($event, $eventId, $delivery_id);
            $consignment = Consignment::where('id', $delivery->event_id)->get()->first();
            $event_sn = $consignment->sn;
            $rsp_arr['depot_id'] = $consignment->send_depot_id;
        } else if(Event::csn_order()->value == $event) {
            // 出貨單號ID
            $delivery = Delivery::getData($event, $eventId)->get()->first();
            $delivery_id = $delivery->id;
            $ord_items_arr = ReceiveDepot::getCSNOrderShipItemWithDeliveryWithReceiveDepotList($event, $eventId, $delivery_id);
            $csn_order = CsnOrder::where('id', $delivery->event_id)->get()->first();
            $event_sn = $csn_order->sn;
            $rsp_arr['depot_id'] = $csn_order->depot_id;
        }

        $rsp_arr['event'] = $event;
        $rsp_arr['delivery'] = $delivery;
        $rsp_arr['delivery_id'] = $delivery_id;
        $rsp_arr['sn'] = $delivery->sn;
        $rsp_arr['ord_items_arr'] = $ord_items_arr;
        $rsp_arr['formAction'] = Route('cms.delivery.store', [
            'deliveryId' => $delivery_id,
        ], true);
        $rsp_arr['breadcrumb_data'] = ['sn' => $event_sn, 'parent' => $event ];

        return view('cms.commodity.delivery.edit', $rsp_arr);
    }

    public function store(Request $request, int $delivery_id)
    {
        $errors = [];
        $delivery = Delivery::where('id', '=', $delivery_id)->get()->first();
        if (null != $delivery->audit_date) {
            $errors['error_msg'] = '不可重複送出審核';
        } else {
            $re = ReceiveDepot::setUpShippingData($delivery->event, $delivery->event_id, $delivery_id, $request->user()->id, $request->user()->name);
            if ($re['success'] == '1') {
                wToast('儲存成功');
                return redirect(Route('cms.delivery.create', [
                    'event' => $delivery->event,
                    'eventId' => $delivery->event_id,
                    ], true));
            }
            $errors['error_msg'] = $re['error_msg'];
        }

        return redirect()->back()->withInput()->withErrors($errors);
    }

    //刪除出貨單
    public function destroy_readyUse(Request $request, $event, int $event_id)
    {
        $re = Delivery::deleteByEventId($event, $event_id);
        if ($re['success'] == '1') {
            wToast('刪除完成');
        } else {
            wToast($re['error_msg']);
        }
        if ($event == Event::order()->value) {
            return redirect(Route('cms.order.detail', [$event_id], true));
        } else if ($event == Event::consignment()->value) {
            return redirect(Route('cms.consignment.edit', [$event_id], true));
        } else if ($event == Event::csn_order()->value) {
            return redirect(Route('cms.consignment.edit', [$event_id], true));
        } else {
            return redirect(Route('cms.order.detail', [$event_id], true));
        }
    }

    //刪除出貨單收貨倉數量
    public function destroyItem(Request $request, $event, $eventId, int $receiveDepotId)
    {
        ReceiveDepot::deleteById($receiveDepotId);
        wToast('刪除成功');

        return redirect(Route('cms.delivery.create', [
            'event' => $event,
            'eventId' => $eventId,], true));
    }

    public function back($event, $eventId)
    {
        $delivery = Delivery::getData($event, $eventId)->get()->first();
        if (null == $delivery) {
            return abort(404);
        }

        if(Event::order()->value == $event) {
            $sub_order = SubOrders::where('id', $delivery->event_id)->get()->first();
            $rsp_arr['order_id'] = $sub_order->order_id;
        }
        $ord_items = null;

        $dlv_back = DB::table(app(DlvBack::class)->getTable(). ' as dlv_back')
            ->where('dlv_back.delivery_id', '=', $delivery->id)
            ->select('dlv_back.id'
                , 'dlv_back.event_item_id'
                , 'dlv_back.product_style_id'
                , 'dlv_back.product_title'
                , 'dlv_back.sku'
                , 'dlv_back.price'
                , 'dlv_back.origin_qty as origin_qty'
                , 'dlv_back.qty as back_qty'
                , 'dlv_back.memo'
            )->get();
        if (isset($dlv_back) && 0 < count($dlv_back)) {
            $ord_items = $dlv_back;
        } else {
            if(Event::order()->value == $event) {
                $ord_items = DB::table(app(OrderItem::class)->getTable(). ' as ord_item')
                    ->where('ord_item.sub_order_id', '=', $eventId)
                    ->select('ord_item.id as event_item_id'
                        , 'ord_item.product_style_id'
                        , 'ord_item.product_title'
                        , 'ord_item.sku'
                        , 'ord_item.price'
                        , 'ord_item.qty as origin_qty'
                    )->get();
            }
        }

        $rsp_arr['delivery'] = $delivery;
        $rsp_arr['event'] = $event;
        $rsp_arr['eventId'] = $eventId;
        $rsp_arr['ord_items'] = $ord_items;
        $rsp_arr['formAction'] = Route('cms.delivery.back_store', [
            'deliveryId' => $delivery->id,
        ], true);
        $rsp_arr['breadcrumb_data'] = ['sn' => $delivery->sn, 'parent' => $event ];

        return view('cms.commodity.delivery.back', $rsp_arr);
    }

    //刪除退貨
    public function back_delete(Request $request, int $delivery_id) {
        Delivery::where('id', $delivery_id)->update([
            'back_date' => null
            , 'back_memo' => null
        ]);
        DlvBack::where('delivery_id', $delivery_id)->delete();

        wToast('刪除成功');
        return redirect()->back();
    }

    public function back_store(Request $request, int $delivery_id) {
        $request->validate([
            'dlv_memo' => 'nullable|string',
            'id.*' => 'nullable|numeric',
            'event_item_id.*' => 'required|numeric',
            'product_style_id.*' => 'required|string',
            'product_title.*' => 'required|string',
            'sku.*' => 'required|string',
            'price.*' => 'required|numeric',
            'origin_qty.*' => 'required|numeric',
            'back_qty.*' => 'required|numeric',
            'memo.*' => 'nullable|string',
        ]);

        $errors = [];
        $msg = DB::transaction(function () use ($request, $delivery_id) {
            $dlv_memo = $request->input('dlv_memo', null);
            Delivery::where('id', $delivery_id)->update([
                'back_date' => date("Y-m-d H:i:s")
                , 'back_memo' => $dlv_memo
            ]);
            $input_items = $request->only('id', 'event_item_id', 'product_style_id', 'product_title', 'sku', 'price', 'origin_qty', 'back_qty', 'memo');
            if (isset($input_items['id']) && 0 < count($input_items['id'])) {
                if(true == isset($input_items['id'][0])) {
                    //已有資料 做編輯
                    for($i = 0; $i < count($input_items['id']); $i++) {
                        DlvBack::where('id', '=', $input_items['id'][$i])->update([
                            'qty' => $input_items['back_qty'][$i],
                            'memo' => $input_items['memo'][$i],
                        ]);
                    }
                } else {
                    $data = [];
                    for($i = 0; $i < count($input_items['id']); $i++) {
                        $data[] = [
                            'delivery_id' => $delivery_id,
                            'event_item_id' => $input_items['event_item_id'][$i],
                            'product_style_id' => $input_items['product_style_id'][$i],
                            'sku' => $input_items['sku'][$i],
                            'product_title' => $input_items['product_title'][$i],
                            'price' => $input_items['price'][$i],
                            'origin_qty' => $input_items['origin_qty'][$i],
                            'qty' => $input_items['back_qty'][$i],
                            'memo' => $input_items['memo'][$i],
                        ];
                    }
                    DlvBack::insert($data);
                }
            }
            wToast('儲存成功');
            return ['success' => 1];
        });

        return redirect()->back()->withInput()->withErrors($errors);
    }

    public function back_inbound($event, $eventId)
    {
        $rsp_arr = [
            'event' => $event,
            'eventId' => $eventId,
        ];
        $delivery = null;
        $delivery_id = null;
        $event_sn = '';
        if(Event::order()->value == $event) {
            $sub_order = SubOrders::getListWithShiGroupById($eventId)->get()->first();
            $event_sn = $sub_order->sn;
            if (null == $sub_order) {
                return abort(404);
            }
            $rsp_arr['order_id'] = $sub_order->order_id;

            // 出貨單號ID
            $delivery = Delivery::getData($event, $sub_order->id)->get()->first();
            $delivery_id = $delivery->id;
            $ord_items_arr = ReceiveDepot::getOrderShipItemWithDeliveryWithReceiveDepotList($event, $eventId, $delivery_id);
//            dd($sub_order, $ord_items_arr->toArray());
        } else if(Event::consignment()->value == $event) {
            // 出貨單號ID
            $delivery = Delivery::getData($event, $eventId)->get()->first();
            $delivery_id = $delivery->id;
            $ord_items_arr = ReceiveDepot::getCSNShipItemWithDeliveryWithReceiveDepotList($event, $eventId, $delivery_id);
            $consignment = Consignment::where('id', $delivery->event_id)->get()->first();
            $event_sn = $consignment->sn;
            $rsp_arr['depot_id'] = $consignment->send_depot_id;
        } else if(Event::csn_order()->value == $event) {
            // 出貨單號ID
            $delivery = Delivery::getData($event, $eventId)->get()->first();
            $delivery_id = $delivery->id;
            $ord_items_arr = ReceiveDepot::getCSNOrderShipItemWithDeliveryWithReceiveDepotList($event, $eventId, $delivery_id);
            $csn_order = CsnOrder::where('id', $delivery->event_id)->get()->first();
            $event_sn = $csn_order->sn;
            $rsp_arr['depot_id'] = $csn_order->depot_id;
        }

        $rsp_arr['event'] = $event;
        $rsp_arr['delivery'] = $delivery;
        $rsp_arr['delivery_id'] = $delivery_id;
        $rsp_arr['sn'] = $delivery->sn;
        $rsp_arr['ord_items_arr'] = $ord_items_arr;
        $rsp_arr['formAction'] = Route('cms.delivery.back_inbound_store', [
            'deliveryId' => $delivery_id,
        ], true);
        $rsp_arr['breadcrumb_data'] = ['sn' => $event_sn, 'parent' => $event ];

        return view('cms.commodity.delivery.back_inbound', $rsp_arr);
    }

    public function back_inbound_store(Request $request, int $delivery_id)
    {
        dd('back_inbound_store', $request->all());
        $request->validate([
            'id' => 'required|numeric',
            'back_qty' => 'required|numeric',
        ]);

        $items_to_back = $request->only('id', 'back_qty', 'memo');
        if (count($items_to_back['id']) != count($items_to_back['back_qty']) && count($items_to_back['id']) != count($items_to_back['memo'])) {
            $errors['error_msg'] = '各資料個數不同';
        }

        $delivery = Delivery::where('id', '=', $delivery_id)->get()->first();
        if (null == $delivery) {
            return abort(404);
        }
        //判斷OK後 回寫入各出貨商品的product_style_id prd_type combo_id
        $bdcisc = ReceiveDepot::checkBackDlvComboItemSameCount($delivery_id, $items_to_back);
        dd($request->all(), $bdcisc);
        if ($bdcisc['success'] == '1') {
            $msg = DB::transaction(function () use ($delivery, $bdcisc, $request) {
                if(isset($bdcisc['data']) && 0 < count($bdcisc['data'])) {
                    foreach ($bdcisc['data'] as $rcv_depot_item) {
//                        dd($rcv_depot_item->memo ?? null);
                        //增加back_num
                        ReceiveDepot::where('id', $rcv_depot_item->id)->update(['back_qty' => DB::raw("back_qty + $rcv_depot_item->qty")]);
                        //加回對應入庫單num
                        $update_arr = [];
                        if (Event::order()->value == $delivery->event || Event::ord_pickup()->value == $delivery->event) {
                            $update_arr['sale_num'] = DB::raw("sale_num - $rcv_depot_item->qty");
                            //TODO 自取可能有入庫 若有入庫過 則需判斷退貨的數量 不得大於後面入庫扣除售出之類的數量
                            // 並須把後面入庫單的退貨數量更新
                            if (Event::ord_pickup()->value == $delivery->event) {
                                DB::rollBack();
                                return ['success' => 0, 'error_msg' => '訂單自取暫無退貨功能'];
                                $pcsInbound = DB::table(app(PurchaseInbound::class)->getTable(). ' as inbound')
                                    ->where('inbound.event', '=', Event::ord_pickup()->value)
                                    ->where('inbound.event_id', '=', $rcv_depot_item->id)
                                    ->whereNull('inbound.deleted_at')
                                    ->select(
                                        'inbound.event'
                                        , 'inbound.event_id'
                                        , DB::raw('(sum(inbound.inbound_num) - sum(inbound.sale_num) - sum(inbound.csn_num) - sum(inbound.consume_num) - sum(inbound.back_num) - sum(inbound.scrap_num)) as total_qty')
                                    )
                                    ->groupBy('inbound.event')
                                    ->groupBy('inbound.event_id')
                                    ->groupBy('inbound.product_style_id')
                                ;
                            }
                        } else if (Event::consignment()->value == $delivery->event) {
                            DB::rollBack();
                            return ['success' => 0, 'error_msg' => '寄倉暫無退貨功能'];
                            //TODO 寄倉可能有入庫 若有入庫過 須先把那邊的入庫退貨
                            $update_arr['csn_num'] = DB::raw("csn_num - $rcv_depot_item->qty");
                        } else if (Event::csn_order()->value == $delivery->event) {
                            DB::rollBack();
                            return ['success' => 0, 'error_msg' => '寄倉訂購暫無退貨功能'];
                            $update_arr['sale_num'] = DB::raw("sale_num - $rcv_depot_item->qty");
                        }
                        PurchaseInbound::where('id', $rcv_depot_item->inbound_id)->update($update_arr);

                        //寫入LOG
                        $rePcsLSC = PurchaseLog::stockChange($delivery->event_id, $rcv_depot_item->product_style_id, $delivery->event, $rcv_depot_item->id
                            , LogEventFeature::send_back()->value, $rcv_depot_item->inbound_id, $rcv_depot_item->qty, $rcv_depot_item->memo ?? null
                            , $rcv_depot_item->product_title, $rcv_depot_item->prd_type
                            , $request->user()->id, $request->user()->name);
                        if ($rePcsLSC['success'] == 0) {
                            DB::rollBack();
                            return $rePcsLSC;
                        }
                        //訂單、寄倉 須將通路庫存加回
                        //若為理貨倉can_tally 需修改通路庫存
                        $inboundData = DB::table('pcs_purchase_inbound as inbound')
                            ->leftJoin('depot', 'depot.id', 'inbound.depot_id')
                            ->where('inbound.id', '=', $rcv_depot_item->inbound_id)
                            ->whereNull('inbound.deleted_at');
                        $inboundDataGet = $inboundData->get()->first();
                        if ($inboundDataGet->can_tally
                            && (Event::order()->value == $delivery->event
                                || Event::ord_pickup()->value == $delivery->event
                                || Event::consignment()->value == $delivery->event)
                        ) {
                            $memo = $rcv_depot_item->memo ?? '';
                            $rePSSC = ProductStock::stockChange($inboundDataGet->product_style_id, $rcv_depot_item->qty
                                , StockEvent::send_back()->value, $delivery->event_id
                                , $request->user()->name. ' '. $delivery->sn. ' ' . $memo
                                , false, $inboundDataGet->can_tally);
                            if ($rePSSC['success'] == 0) {
                                DB::rollBack();
                                return $rePSSC;
                            }
                        }
                    }
                }
                return ['success' => 1];
            });
            if ($msg['success'] == 0) {
                throw ValidationException::withMessages(['item_error' => $msg['error_msg']]);
            } else {
                wToast('儲存成功');
                return redirect(Route('cms.delivery.back_inbound', [
                    'event' => $delivery->event,
                    'eventId' => $delivery->event_id,
                ], true));
            }
        } else {
            throw ValidationException::withMessages(['item_error' => $bdcisc['error_msg']]);
        }
    }

}
