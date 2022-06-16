<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Delivery\Event;
use App\Enums\Delivery\LogisticStatus;
use App\Http\Controllers\Controller;
use App\Models\Consignment;
use App\Models\CsnOrder;
use App\Models\Delivery;
use App\Models\Depot;
use App\Models\ReceiveDepot;
use App\Models\ShipmentCategory;
use App\Models\SubOrders;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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
        $cond['ship_category'] = Arr::get($query, 'ship_category', '');

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
            $delivery = Delivery::getList($cond)->paginate($cond['data_per_page']);
            $cond['ship_method'] = $ship_method;
            $cond['ship_category'] = $ship_category;
        } else {
            $delivery = Delivery::getList($cond)->paginate($cond['data_per_page']);
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
}
