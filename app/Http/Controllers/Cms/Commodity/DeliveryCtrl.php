<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Delivery\BackStatus;
use App\Enums\Delivery\Event;
use App\Enums\Delivery\LogisticStatus;
use App\Enums\Order\OrderStatus;
use App\Enums\Purchase\LogEventFeature;
use App\Enums\StockEvent;
use App\Enums\Supplier\Payment;
use App\Http\Controllers\Controller;
use App\Models\AllGrade;
use App\Models\AccountPayable;
use App\Models\Consignment;
use App\Models\ConsignmentItem;
use App\Models\CsnOrder;
use App\Models\CsnOrderFlow;
use App\Models\CsnOrderItem;
use App\Models\Delivery;
use App\Models\Depot;
use App\Models\DlvBack;
use App\Models\GeneralLedger;
use App\Models\Logistic;
use App\Models\Order;
use App\Models\OrderFlow;
use App\Models\OrderInvoice;
use App\Models\OrderItem;
use App\Models\PayingOrder;
use App\Models\PayableAccount;
use App\Models\PayableCash;
use App\Models\PayableCheque;
use App\Models\PayableRemit;
use App\Models\PayableForeignCurrency;
use App\Models\PayableOther;
use App\Models\PayableDefault;
use App\Models\ProductStock;
use App\Models\PurchaseInbound;
use App\Models\PurchaseLog;
use App\Models\ReceivedDefault;
use App\Models\ReceiveDepot;
use App\Models\ShipmentCategory;
use App\Models\ShipmentGroup;
use App\Models\SubOrders;
use App\Models\User;
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
        $delivery = Delivery::where('id', '=', $delivery_id)->first();
        Delivery::where('id', $delivery_id)->update([
            'back_date' => null
            , 'back_sn' => null
            , 'back_memo' => null
            , 'back_user_id' => null
            , 'back_user_name' => null
        ]);
        Delivery::changeBackStatus($delivery_id, BackStatus::del_back());
        if (Event::order()->value == $delivery->event) {
            OrderFlow::changeOrderStatus($delivery->event_id, OrderStatus::CancleBack());
        }
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
        $delivery = Delivery::where('id', $delivery_id)->first();
        $msg = DB::transaction(function () use ($request, $delivery, $delivery_id) {
            $dlv_memo = $request->input('dlv_memo', null);
            $back_sn = str_replace("DL","BK",$delivery->sn); //將出貨單號改為銷貨退回單號
            Delivery::where('id', $delivery_id)->update([
                'back_date' => date("Y-m-d H:i:s")
                , 'back_sn' => $back_sn //寫入銷貨退回單號
                , 'back_memo' => $dlv_memo
                , 'back_user_id' => $request->user()->id
                , 'back_user_name' => $request->user()->name
            ]);
            Delivery::changeBackStatus($delivery_id, BackStatus::add_back());
            if (Event::order()->value == $delivery->event) {
                OrderFlow::changeOrderStatus($delivery->event_id, OrderStatus::BackProcessing());
            } else if (Event::consignment()->value == $delivery->event) {
                DB::rollBack();
                return ['success' => 0, 'error_msg' => '寄倉暫無退貨功能'];
            } else if (Event::csn_order()->value == $delivery->event) {
                DB::rollBack();
                return ['success' => 0, 'error_msg' => '寄倉訂購暫無退貨功能'];
                CsnOrderFlow::changeOrderStatus($delivery->event_id, OrderStatus::BackProcessing());
            }
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
            return ['success' => 1];
        });
        if ($msg['success'] == 0) {
            throw ValidationException::withMessages(['item_error' => $msg['error_msg']]);
        } else {
            wToast('儲存成功');
            return redirect(Route('cms.delivery.back_detail', [
                'event' => $delivery->event,
                'eventId' => $delivery->event_id,
            ], true));
        }
    }

    //銷貨退回明細
    public function back_detail($event, $eventId)
    {
        $rsp_arr = [];
        $delivery = Delivery::getData($event, $eventId)->get()->first();
        if (null == $delivery) {
            return abort(404);
        }
        $item_table = null;
        $dlvBack = null;
        $order = null;
        $orderInvoice = null;
        $logistic = null;
        if (Event::order()->value == $delivery->event) {
            $subOrder = SubOrders::where('id', '=', $delivery->event_id)->first();
            $order = Order::orderDetail($subOrder->order_id)->get()->first();
            $orderInvoice = OrderInvoice::where('source_type', '=', app(Order::class)->getTable())
                ->where('source_id', '=', $subOrder->order_id)->first();
            $item_table = app(OrderItem::class)->getTable();
            $rsp_arr['order'] = $order;
            $rsp_arr['orderInvoice'] = $orderInvoice;
        } else if (Event::consignment()->value == $delivery->event) {
            $item_table = app(ConsignmentItem::class)->getTable();
            return abort(404);
        } else if (Event::csn_order()->value == $delivery->event) {
            $item_table = app(CsnOrderItem::class)->getTable();
            return abort(404);
        }

        if (isset($item_table)) {
            $dlvBack = DB::table(app(DlvBack::class)->getTable(). ' as dlv_back')
                ->leftJoin($item_table. ' as item_tb', 'item_tb.id', '=', 'dlv_back.event_item_id')
                ->select(
                    'dlv_back.id'
                    , 'dlv_back.event_item_id'
                    , 'dlv_back.product_style_id'
                    , 'dlv_back.sku'
                    , 'dlv_back.product_title'
                    , 'dlv_back.price'
                    , 'dlv_back.qty'
                    , 'dlv_back.memo'
                )
            ;
            if (Event::order()->value == $delivery->event) {
                $dlvBack->addSelect(
                    DB::raw('ifnull(item_tb.unit_cost, "") as uni_cost')
                    , DB::raw('ifnull(item_tb.bonus, "") as bonus')
                );

                $dlvBack->where('dlv_back.delivery_id', $delivery->id);
            }
            $dlvBack = $dlvBack->get();
        }
//        $logistic = Logistic::where('id', '=', $delivery->event_id)->first();

        $logistic = DB::table(app(Logistic::class)->getTable(). ' as lgt_tb')
            ->leftJoin(app(ShipmentGroup::class)->getTable(). ' as shi_group', 'shi_group.id', '=', 'lgt_tb.ship_group_id')
            ->select(
                'lgt_tb.*', 'shi_group.name as group_name'
            )
            ->where('lgt_tb.delivery_id', '=', $delivery->id)
            ->first();
        $ord_items_arr = ReceiveDepot::getRcvDepotBackQty($delivery->id, $delivery->event, $delivery->event_id);

        if (isset($ord_items_arr) && 0 < count($ord_items_arr)) {
            $sendBackData = PurchaseLog::getSendBackData($delivery->id, $delivery->event_id);
            //整合退貨入庫時 輸入的說明
            if (isset($sendBackData) && 0 < count(($sendBackData))) {
                foreach ($ord_items_arr as $key => $ord_item) {
                    if (isset($ord_item->receive_depot) && 0 < count($ord_item->receive_depot)) {
                        foreach ($ord_item->receive_depot as $key_rcv_depot => $rcv_depot) {
                            foreach ($sendBackData as $key_send_back => $val_send_back) {
                                if ($rcv_depot->id == $val_send_back->event_id) {
                                    $ord_item->receive_depot[$key_rcv_depot]->memo = $val_send_back->note;
                                }
                            }
                        }
                    }
                }
            }
            //移除數量為空的資料
            foreach ($ord_items_arr as $key => $ord_item) {
                if (isset($ord_item->receive_depot) && 0 < count($ord_item->receive_depot)) {
                    foreach ($ord_item->receive_depot as $key_rcv_depot => $rcv_depot) {
                        if (0 == ($rcv_depot->back_qty)) {
                            unset($ord_item->receive_depot[$key_rcv_depot]);
                        }
                    }
                }
                if (isset($ord_item->receive_depot) && 0 == count($ord_item->receive_depot)) {
                    unset($ord_items_arr[$key]);
                }
            }
            $ord_items_arr = json_decode($ord_items_arr);
            $ord_items_arr = array_values((array)$ord_items_arr);
        }

        $rsp_arr['logistic'] = $logistic;

        $rsp_arr['event'] = $event;
        $rsp_arr['delivery'] = $delivery;
        $rsp_arr['delivery_id'] = $delivery->id;
        $rsp_arr['sn'] = $delivery->sn;
        $rsp_arr['dlvBack'] = $dlvBack;
        $rsp_arr['ord_items_arr'] = $ord_items_arr;
        $rsp_arr['breadcrumb_data'] = ['sn' => $delivery->event_sn, 'parent' => $event ];
        $back_item = Delivery::back_item($delivery->id)->get();
        foreach ($back_item as $key => $value) {
            $back_item[$key]->delivery_back_items = json_decode($value->delivery_back_items);
        }
        $rsp_arr['back_item'] = $back_item->first();
        return view('cms.commodity.delivery.back_detail', $rsp_arr);
    }

    public function back_inbound($event, $eventId)
    {
        $rsp_arr = [
            'event' => $event,
            'eventId' => $eventId,
        ];
        // 出貨單號ID
        $delivery = Delivery::getData($event, $eventId)->get()->first();
        $delivery_id = $delivery->id;
        $event_sn = $delivery->event_sn;

        if(Event::order()->value == $event) {
            $sub_order = SubOrders::getListWithShiGroupById($eventId)->get()->first();
            if (null == $sub_order) {
                return abort(404);
            }
            $rsp_arr['order_id'] = $sub_order->order_id;
        } else if(Event::consignment()->value == $event) {
            $consignment = Consignment::where('id', $delivery->event_id)->get()->first();
            $rsp_arr['depot_id'] = $consignment->send_depot_id;
        } else if(Event::csn_order()->value == $event) {
            $csn_order = CsnOrder::where('id', $delivery->event_id)->get()->first();
            $rsp_arr['depot_id'] = $csn_order->depot_id;
        }
        $ord_items_arr = ReceiveDepot::getRcvDepotBackQty($delivery->id, $delivery->event, $delivery->event_id);

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
        $request->validate([
            'id' => 'required',
            'back_qty' => 'required',
        ]);

        $items_to_back = $request->only('id', 'back_qty', 'memo');
        if (count($items_to_back['id']) != count($items_to_back['back_qty']) && count($items_to_back['id']) != count($items_to_back['memo'])) {
            throw ValidationException::withMessages(['error_msg' => '各資料個數不同']);
        }

        $delivery = Delivery::where('id', '=', $delivery_id)->get()->first();
        if (null == $delivery) {
            return abort(404);
        }


        //判斷OK後 回寫入各出貨商品的product_style_id prd_type combo_id
        $bdcisc = ReceiveDepot::checkBackDlvComboItemSameCount($delivery_id, $items_to_back);
//        dd($request->all(), $bdcisc);
        if ($bdcisc['success'] == '1') {
            $msg = DB::transaction(function () use ($delivery, $bdcisc, $request) {
                Delivery::where('id', '=', $delivery->id)->update([
                    'back_inbound_user_id' => $request->user()->id
                    , 'back_inbound_user_name' => $request->user()->name
                    , 'back_inbound_date' => date("Y-m-d H:i:s")
                ]);
                Delivery::changeBackStatus($delivery->id, BackStatus::add_back_inbound());

                //直接依據退貨數量 寫回出貨單組合包的退貨數量
                $dlvBack = DB::table(app(DlvBack::class)->getTable(). ' as dlv_back')
                    ->leftJoin(app(ReceiveDepot::class)->getTable(). ' as rcv_depot', function ($join) {
                        $join->on('rcv_depot.delivery_id', '=', 'dlv_back.delivery_id')
                            ->on('rcv_depot.event_item_id', '=', 'dlv_back.event_item_id');
                    })
                    ->where('rcv_depot.prd_type', '=', 'c')
                    ->where('dlv_back.qty', '>', 0)
                    ->select(
                        'rcv_depot.id as rcv_depot_id'
                        , 'dlv_back.event_item_id'
                        , 'dlv_back.product_style_id'
                        , 'dlv_back.qty'
                    )
                    ->get();
                if (isset($dlvBack) && 0 < count($dlvBack)) {
                    foreach ($dlvBack as $key_back => $val_back) {
                        ReceiveDepot::where('id', '=', $val_back->rcv_depot_id)->update([
                            'back_qty' => $val_back->qty
                        ]);
                    }
                }

                if(isset($bdcisc['data']) && 0 < count($bdcisc['data']) && isset($bdcisc['data']['id']) && 0 < count($bdcisc['data']['id'])) {
                    for ($num_bdcisc = 0; $num_bdcisc < count($bdcisc['data']['id']); $num_bdcisc++) {
                        $rcv_depot_item = new \stdClass();
                        $rcv_depot_item->id = $bdcisc['data']['id'][$num_bdcisc];
                        $rcv_depot_item->back_qty = $bdcisc['data']['back_qty'][$num_bdcisc];
                        $rcv_depot_item->memo = $bdcisc['data']['memo'][$num_bdcisc];
                        $rcv_depot_item->product_style_id = $bdcisc['data']['product_style_id'][$num_bdcisc];
                        $rcv_depot_item->product_title = $bdcisc['data']['product_title'][$num_bdcisc];
                        $rcv_depot_item->inbound_id = $bdcisc['data']['inbound_id'][$num_bdcisc];
                        $rcv_depot_item->prd_type = $bdcisc['data']['prd_type'][$num_bdcisc];
                        $rcv_depot_item->combo_id = $bdcisc['data']['combo_id'][$num_bdcisc];

                        //增加back_num
                        ReceiveDepot::where('id', $rcv_depot_item->id)->update(['back_qty' => DB::raw("back_qty + $rcv_depot_item->back_qty")]);
                        //加回對應入庫單num
                        $update_arr = [];
                        if (Event::order()->value == $delivery->event || Event::ord_pickup()->value == $delivery->event) {
                            OrderFlow::changeOrderStatus($delivery->event_id, OrderStatus::Backed());
                            $update_arr['sale_num'] = DB::raw("sale_num - $rcv_depot_item->back_qty");
                            //TODO 自取可能有入庫 若有入庫過 則需判斷退貨的數量 不得大於後面入庫扣除售出之類的數量
                            // 並須把後面入庫單的退貨數量更新
                            if (Event::ord_pickup()->value == $delivery->event) {
                                DB::rollBack();
                                return ['success' => 0, 'error_msg' => '訂單自取暫無退貨入庫功能'];
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
                            return ['success' => 0, 'error_msg' => '寄倉暫無退貨入庫功能'];
                            //TODO 寄倉可能有入庫 若有入庫過 須先把那邊的入庫退貨
                            $update_arr['csn_num'] = DB::raw("csn_num - $rcv_depot_item->back_qty");
                        } else if (Event::csn_order()->value == $delivery->event) {
                            DB::rollBack();
                            return ['success' => 0, 'error_msg' => '寄倉訂購暫無退貨入庫功能'];
                            $update_arr['sale_num'] = DB::raw("sale_num - $rcv_depot_item->back_qty");
                        }
                        PurchaseInbound::where('id', $rcv_depot_item->inbound_id)->update($update_arr);

                        //寫入LOG
                        $rePcsLSC = PurchaseLog::stockChange($delivery->event_id, $rcv_depot_item->product_style_id, $delivery->event, $rcv_depot_item->id
                            , LogEventFeature::send_back()->value, $rcv_depot_item->inbound_id, $rcv_depot_item->back_qty, $rcv_depot_item->memo ?? null
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
                            $rePSSC = ProductStock::stockChange($inboundDataGet->product_style_id, $rcv_depot_item->back_qty
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
                throw ValidationException::withMessages(['error_msg' => $msg['error_msg']]);
            } else {
                wToast('儲存成功');
                return redirect(Route('cms.delivery.back_detail', [
                    'event' => $delivery->event,
                    'eventId' => $delivery->event_id,
                ], true));
            }
        } else {
            throw ValidationException::withMessages(['error_msg' => $bdcisc['error_msg']]);
        }
    }

    public function back_inbound_delete(Request $request, int $delivery_id) {
        $delivery = Delivery::where('id', '=', $delivery_id)->get()->first();
        if (false == isset($delivery) || false == isset($delivery->back_inbound_date)) {
            return abort(404);
        }
        $rcv_depot = DB::table(app(Delivery::class)->getTable(). ' as dlv_tb')
            ->leftJoin(app(ReceiveDepot::class)->getTable(). ' as rcv_depot', 'rcv_depot.delivery_id', '=', 'dlv_tb.id')
            ->select(
                'rcv_depot.id'
                , 'rcv_depot.back_qty'
                , 'rcv_depot.product_style_id'
                , 'rcv_depot.product_title'
                , 'rcv_depot.inbound_id'
                , 'rcv_depot.prd_type'
                , 'rcv_depot.combo_id'
            )
            ->where('rcv_depot.back_qty', '>', 0)
            ->get();
        if (isset($rcv_depot) && 0 < count($rcv_depot)) {
            $msg = DB::transaction(function () use ($delivery, $rcv_depot, $request) {
                Delivery::where('id', '=', $delivery->id)->update([
                    'back_inbound_user_id' => null
                    , 'back_inbound_user_name' => null
                    , 'back_inbound_date' => null
                ]);
                Delivery::changeBackStatus($delivery->id, BackStatus::del_back_inbound());
                //直接依據退貨數量 寫回出貨單組合包的退貨數量
                $dlvBack = DB::table(app(DlvBack::class)->getTable(). ' as dlv_back')
                    ->leftJoin(app(ReceiveDepot::class)->getTable(). ' as rcv_depot', function ($join) {
                        $join->on('rcv_depot.delivery_id', '=', 'dlv_back.delivery_id')
                            ->on('rcv_depot.event_item_id', '=', 'dlv_back.event_item_id');
                    })
                    ->where('rcv_depot.prd_type', '=', 'c')
                    ->where('dlv_back.qty', '>', 0)
                    ->select(
                        'rcv_depot.id as rcv_depot_id'
                        , 'dlv_back.event_item_id'
                        , 'dlv_back.product_style_id'
                        , 'dlv_back.qty'
                    )
                    ->get();

                foreach ($rcv_depot as $key_rcv => $val_rcv) {
                    //減少back_num
                    ReceiveDepot::where('id', $val_rcv->id)->update(['back_qty' => DB::raw("back_qty - $val_rcv->back_qty")]);
                    //減回對應入庫單num
                    $update_arr = [];
                    if (Event::order()->value == $delivery->event || Event::ord_pickup()->value == $delivery->event) {
                        OrderFlow::changeOrderStatus($delivery->event_id, OrderStatus::CancleBack());
                        $update_arr['sale_num'] = DB::raw("sale_num + $val_rcv->back_qty");
                        //TODO 自取可能有入庫 若有入庫過 則需判斷退貨的數量 不得大於後面入庫扣除售出之類的數量
                        // 並須把後面入庫單的退貨數量更新
                        if (Event::ord_pickup()->value == $delivery->event) {
                            DB::rollBack();
                            return ['success' => 0, 'error_msg' => '訂單自取暫無退貨入庫功能'];
                        }
                    } else if (Event::consignment()->value == $delivery->event) {
                        DB::rollBack();
                        return ['success' => 0, 'error_msg' => '寄倉暫無退貨入庫功能'];
                        $update_arr['csn_num'] = DB::raw("csn_num + $val_rcv->back_qty");
                    } else if (Event::csn_order()->value == $delivery->event) {
                        DB::rollBack();
                        return ['success' => 0, 'error_msg' => '寄倉訂購暫無退貨入庫功能'];
                        $update_arr['sale_num'] = DB::raw("sale_num + $val_rcv->back_qty");
                    }
                    PurchaseInbound::where('id', $val_rcv->inbound_id)->update($update_arr);

                    //寫入LOG
                    $rePcsLSC = PurchaseLog::stockChange($delivery->event_id, $val_rcv->product_style_id, $delivery->event, $val_rcv->id
                        , LogEventFeature::send_back_cancle()->value, $val_rcv->inbound_id, $val_rcv->back_qty * -1, $val_rcv->memo ?? null
                        , $val_rcv->product_title, $val_rcv->prd_type
                        , $request->user()->id, $request->user()->name);
                    if ($rePcsLSC['success'] == 0) {
                        DB::rollBack();
                        return $rePcsLSC;
                    }
                    //訂單、寄倉 須將通路庫存減回
                    //若為理貨倉can_tally 需修改通路庫存
                    $inboundData = DB::table('pcs_purchase_inbound as inbound')
                        ->leftJoin('depot', 'depot.id', 'inbound.depot_id')
                        ->where('inbound.id', '=', $val_rcv->inbound_id)
                        ->whereNull('inbound.deleted_at');
                    $inboundDataGet = $inboundData->get()->first();
                    if (isset($inboundDataGet) && isset($inboundDataGet->can_tally) && $inboundDataGet->can_tally
                        && (Event::order()->value == $delivery->event
                            || Event::ord_pickup()->value == $delivery->event
                            || Event::consignment()->value == $delivery->event)
                    ) {
                        $memo = '';
                        $rePSSC = ProductStock::stockChange($inboundDataGet->product_style_id, $val_rcv->back_qty * -1
                            , StockEvent::send_back_cancle()->value, $delivery->event_id
                            , $request->user()->name. ' '. $delivery->sn. ' ' . $memo
                            , false, $inboundDataGet->can_tally);
                        if ($rePSSC['success'] == 0) {
                            DB::rollBack();
                            return $rePSSC;
                        }
                    }
                }

                return ['success' => 1];
            });
            if ($msg['success'] == 0) {
                throw ValidationException::withMessages(['error_msg' => $msg['error_msg']]);
            } else {
                wToast('儲存成功');
                return redirect()->back()->withInput();
            }
        } else {
            throw ValidationException::withMessages(['error_msg' => '無可退貨入庫數量']);
        }
    }

    public function return_pay_order(Request $request, $id)
    {
        $request->merge([
            'id' => $id,
        ]);

        $request->validate([
            'id' => 'required|exists:dlv_delivery,id',
        ]);

        $source_type = app(Delivery::class)->getTable();
        $type = 9;

        $paying_order = PayingOrder::where([
            'source_type' => $source_type,
            'source_id' => $id,
            'source_sub_id' => null,
            'type' => $type,
            'deleted_at' => null,
        ])->first();

        $delivery = Delivery::back_item($id)->get();
        foreach ($delivery as $key => $value) {
            $delivery[$key]->delivery_back_items = json_decode($value->delivery_back_items);
        }
        $delivery = $delivery->first();

        if (!$paying_order) {
            $product_grade = ReceivedDefault::where('name', '=', 'product')->first()->default_grade_id;
            $logistics_grade = ReceivedDefault::where('name', '=', 'logistics')->first()->default_grade_id;

            $result = PayingOrder::createPayingOrder(
                $source_type,
                $id,
                null,
                $request->user()->id,
                $type,
                $product_grade,
                $logistics_grade,
                $delivery->delivery_back_total_price ?? 0,
                '',
                '',
                $delivery->buyer_id,
                $delivery->buyer_name,
                $delivery->buyer_phone,
                $delivery->buyer_address
            );

            $paying_order = PayingOrder::findOrFail($result['id']);

            $delivery = Delivery::back_item($id)->get();
            foreach ($delivery as $key => $value) {
                $delivery[$key]->delivery_back_items = json_decode($value->delivery_back_items);
            }
            $delivery = $delivery->first();
        }

        $applied_company = DB::table('acc_company')->where('id', 1)->first();

        $product_grade_name = AllGrade::find($paying_order->product_grade_id)->eachGrade->code . ' ' . AllGrade::find($paying_order->product_grade_id)->eachGrade->name;
        $logistics_grade_name = AllGrade::find($paying_order->logistics_grade_id)->eachGrade->code . ' ' . AllGrade::find($paying_order->logistics_grade_id)->eachGrade->name;

        // $order_discount = DB::table('ord_discounts')->where([
        //         'order_type'=>'main',
        //         'order_id'=>request('id'),
        //     ])->where('discount_value', '>', 0)->get()->toArray();
        // foreach($order_discount as $value){
        //     $value->account_code = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->code : '4000';
        //     $value->account_name = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->name : '無設定會計科目';
        // }

        $payable_data = PayingOrder::get_payable_detail($paying_order->id);

        $accountant = User::whereIn('id', $payable_data->pluck('accountant_id_fk')->toArray())->get();
        $accountant = array_unique($accountant->pluck('name')->toArray());
        asort($accountant);

        $undertaker = User::find($paying_order->usr_users_id);

        $zh_price = num_to_str($paying_order->price);

        return view('cms.commodity.delivery.return_pay_order', [
            'breadcrumb_data' => ['event' => $delivery->delivery_event, 'eventId' => $delivery->delivery_event_id, 'sn' => $delivery->delivery_event_sn],

            'paying_order' => $paying_order,
            'payable_data' => $payable_data,
            'delivery' => $delivery,
            // 'order_discount' => $order_discount,
            'applied_company' => $applied_company,
            'product_grade_name' => $product_grade_name,
            'logistics_grade_name' => $logistics_grade_name,
            'accountant'=>implode(',', $accountant),
            'undertaker' => $undertaker,
            'zh_price' => $zh_price,
        ]);
    }

    public function return_pay_create(Request $request, $id)
    {
        $request->merge([
            'id' => $id,
        ]);

        $request->validate([
            'id' => 'required|exists:dlv_delivery,id',
        ]);

        $source_type = app(Delivery::class)->getTable();
        $type = 9;

        $paying_order = PayingOrder::where([
            'source_type' => $source_type,
            'source_id' => $id,
            'source_sub_id' => null,
            'type' => $type,
            'deleted_at' => null,
        ])->first();

        if(! $paying_order) {
            return abort(404);
        }

        $delivery = Delivery::back_item($id)->get();
        foreach ($delivery as $key => $value) {
            $delivery[$key]->delivery_back_items = json_decode($value->delivery_back_items);
        }
        $delivery = $delivery->first();

        if($request->isMethod('post')){
            $request->merge([
                'pay_order_id'=>$paying_order->id,
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
                    'balance_date'=>date("Y-m-d H:i:s"),
                ]);
            }

            if (PayingOrder::find($paying_order->id) && PayingOrder::find($paying_order->id)->balance_date) {
                return redirect()->route('cms.delivery.return-pay-order', [
                    'id' => $delivery->delivery_id,
                ]);

            } else {
                return redirect()->route('cms.delivery.return-pay-create', [
                    'id' => $delivery->delivery_id,
                ]);
            }

        } else {

            if($paying_order->balance_date) {
                return abort(404);
            }

            $product_grade_name = AllGrade::find($paying_order->product_grade_id)->eachGrade->code . ' ' . AllGrade::find($paying_order->product_grade_id)->eachGrade->name;
            $logistics_grade_name = AllGrade::find($paying_order->logistics_grade_id)->eachGrade->code . ' ' . AllGrade::find($paying_order->logistics_grade_id)->eachGrade->name;

            // $order_discount = DB::table('ord_discounts')->where([
            //         'order_type'=>'main',
            //         'order_id'=>request('id'),
            //     ])->where('discount_value', '>', 0)->get()->toArray();
            // foreach($order_discount as $value){
            //     $value->account_code = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->code : '4000';
            //     $value->account_name = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->name : '無設定會計科目';
            // }

            $currency = DB::table('acc_currency')->find($paying_order->acc_currency_fk);
            if(!$currency){
                $currency = (object)[
                    'name'=>'NTD',
                    'rate'=>1,
                ];
            }

            $payable_data = PayingOrder::get_payable_detail($paying_order->id);

            $tw_price = $paying_order->price - $payable_data->sum('tw_price');

            $total_grades = GeneralLedger::total_grade_list();

            return view('cms.commodity.delivery.return_pay_create', [
                'breadcrumb_data' => ['event' => $delivery->delivery_event, 'eventId' => $delivery->delivery_event_id, 'sn' => $delivery->delivery_event_sn, 'id' => $delivery->delivery_id],

                'paying_order' => $paying_order,
                'payable_data' => $payable_data,
                'delivery' => $delivery,
                // 'order_discount' => $order_discount,
                'product_grade_name' => $product_grade_name,
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

                'form_action' => Route('cms.delivery.return-pay-create', ['id' => $delivery->delivery_id]),
                'transactTypeList' => AccountPayable::getTransactTypeList(),
                'chequeStatus' => AccountPayable::getChequeStatus(),
            ]);
        }
    }
}
