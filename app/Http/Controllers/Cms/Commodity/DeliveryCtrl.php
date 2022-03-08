<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Delivery\Event;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Depot;
use App\Models\OrderStatus;
use App\Models\ReceiveDepot;
use App\Models\SubOrders;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class DeliveryCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();
        $cond = [];
        $cond['event_sn'] = Arr::get($query, 'event_sn', null);
        $cond['delivery_sn'] = Arr::get($query, 'delivery_sn', null);
        $cond['receive_depot_id'] = Arr::get($query, 'receive_depot_id', null);
        $cond['ship_method'] = Arr::get($query, 'ship_method', null);
        $cond['logistic_status_code'] = Arr::get($query, 'logistic_status_code', null);

        $cond['order_sdate'] = Arr::get($query, 'order_sdate', null);
        $cond['order_edate'] = Arr::get($query, 'order_edate', null);
        $cond['delivery_sdate'] = Arr::get($query, 'delivery_sdate', null);
        $cond['delivery_edate'] = Arr::get($query, 'delivery_edate', null);

        $cond['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page', 10));

        $delivery = Delivery::getList($cond)->paginate($cond['data_per_page']);

        return view('cms.commodity.delivery.list', [
            'dataList' => $delivery,
            'depotList' => Depot::all(),
            'orderStatus' => OrderStatus::all(),
            'searchParam' => $cond,
            'data_per_page' => $cond['data_per_page']]);
    }

    public function create($sub_order_id)
    {
        $sub_order = SubOrders::getListWithShiGroupById($sub_order_id)->get()->first();
        if (null == $sub_order) {
            return abort(404);
        }

        // 出貨單號ID
        $delivery = Delivery::createData(
            Event::order()->value
            , $sub_order->id
            , $sub_order->sn
            , $sub_order->ship_temp_id
            , $sub_order->ship_temp
            , $sub_order->ship_category
            , $sub_order->ship_category_name
            , $sub_order->ship_group_id);
        $delivery_id = null;
        if (isset($delivery['id'])) {
            $delivery_id = $delivery['id'];
        }

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
        $errors = [];
        $delivery = Delivery::where('id', '=', $delivery_id)->get()->first();
        if (null != $delivery->close_date) {
            $errors['error_msg'] = '不可重複送出審核';
        } else {
            $re = ReceiveDepot::setUpShippingData($delivery_id);
            if ($re['success'] == '1') {
                wToast('儲存完成');
                return redirect(Route('cms.delivery.create', [$delivery->event_id], true));
            }
            $errors['error_msg'] = $re['error_msg'];
        }

//        dd($re['error_msg']);
        return redirect()->back()->withInput()->withErrors($errors);
    }

    public function destroy(Request $request, $deliveryId, int $receiveDepotId)
    {
        ReceiveDepot::deleteById($receiveDepotId);
        wToast('刪除完成');
        return redirect(Route('cms.delivery.create', [$deliveryId], true));
    }
}
