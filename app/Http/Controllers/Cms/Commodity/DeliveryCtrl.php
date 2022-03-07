<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Delivery\Event;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\PurchaseInbound;
use App\Models\ReceiveDepot;
use App\Models\SubOrders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryCtrl extends Controller
{
    public function index(Request $request)
    {
        dd(PurchaseInbound::getInboundList([])->get());
    }

    public function create($sub_order_id)
    {
        $sub_order = SubOrders::getListWithShiGroupById($sub_order_id)->get()->first();
        if (null == $sub_order) {
            return abort(404);
        }

        // 出貨單號ID
        $delivery_id = Delivery::createData(
            Event::order()->value
            , $sub_order->id
            , $sub_order->sn
            , $sub_order->ship_temp_id
            , $sub_order->ship_temp
            , $sub_order->ship_category
            , $sub_order->ship_category_name
            , $sub_order->ship_group_id);

        $delivery = Delivery::where('id', '=', $delivery_id)->get()->first();
        $ord_items_arr = ReceiveDepot::getShipItemWithDeliveryWithReceiveDepotList(Event::order()->value, $sub_order_id, $delivery_id);

        return view('cms.commodity.delivery.edit', [
            'delivery' => $delivery,
            'delivery_id' => $delivery_id,
            'sn' => $sub_order->sn,
            'order_id' => $sub_order->order_id,
            'sub_order_id' => $sub_order_id,
            'ord_items_arr' => $ord_items_arr,
            'formAction' => Route('cms.delivery.create', [$delivery_id], true)
        ]);
    }

    public function store(Request $request, int $delivery_id)
    {
        $delivery = Delivery::where('id', '=', $delivery_id)->get()->first();
        ReceiveDepot::setUpShippingData($delivery_id);
        wToast('儲存完成');
        return redirect(Route('cms.delivery.create', [$delivery->event_id], true));
    }

    public function destroy(Request $request, $deliveryId, int $receiveDepotId)
    {
        ReceiveDepot::deleteById($receiveDepotId);
        wToast('刪除完成');
        return redirect(Route('cms.delivery.create', [$deliveryId], true));
    }
}
