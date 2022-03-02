<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Delivery\Event;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\OrderItem;
use App\Models\ReceiveDepot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryCtrl extends Controller
{
    public function index(Request $request)
    {
    }

    public function create($sub_order_id)
    {
        $sub_order = DB::table('ord_sub_orders')->where('ord_sub_orders.id', $sub_order_id)->get()->first();

        // 出貨單號ID
        $delivery_id = Delivery::createData(
            Event::order()->value
            , $sub_order->id
            , $sub_order->sn
            , $sub_order->ship_temp_id
            , $sub_order->ship_temp
            , $sub_order->ship_category
            , $sub_order->ship_category_name);

        // 子訂單的商品列表
        $ord_items = OrderItem::getShipItem($sub_order_id)->get();
        // 子訂單的入庫資料
        $ord_items_arr = null;
        if (null != $ord_items && 0 < count($ord_items)) {
            $receiveDepotList = ReceiveDepot::getDeliveryWithReceiveDepotList(Event::order()->value, $sub_order_id, $delivery_id)->get();
            $ord_items_arr = $ord_items->toArray();
            foreach ($ord_items_arr as $ord_key => $ord_item) {
                $ord_items_arr[$ord_key]->receive_depot = [];
            }
            if (0 < count($receiveDepotList)) {
                $receiveDepotList_arr = $receiveDepotList->toArray();
                foreach ($ord_items_arr as $ord_key => $ord_item) {
                    $ord_items_arr[$ord_key]->receive_depot = [];
                    foreach ($receiveDepotList_arr as $revd_key => $revd_item) {
                        if ($ord_items_arr[$ord_key]->item_id == $revd_item->event_item_id
                            && $ord_items_arr[$ord_key]->product_style_id == $revd_item->product_style_id
                        ) {
                            array_push($ord_items_arr[$ord_key]->receive_depot, $receiveDepotList_arr[$revd_key]);
                            unset($receiveDepotList_arr[$revd_key]);
                        }
                    }
                }
            }
        }

        return view('cms.commodity.delivery.edit', [
            'sn' => $sub_order->sn,
            'order_id' => $sub_order->order_id,
            'sub_order_id' => $sub_order_id,
            'ord_items' => $ord_items,
            'ord_items_arr' => $ord_items_arr
        ]);
    }

    public function store(Request $request, int $sub_order_id)
    {
    }

    public function destroy(Request $request, int $sub_order_id)
    {
    }
}
