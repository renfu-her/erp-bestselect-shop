<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Delivery\Event;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
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

        $delivery_id = Delivery::createData(
            Event::order()->value
            , $sub_order->id
            , $sub_order->sn
            , $sub_order->ship_temp_id
            , $sub_order->ship_temp
            , $sub_order->ship_category
            , $sub_order->ship_category_name);

        $ord_items = DB::table('ord_items')->where('ord_items.sub_order_id', $sub_order_id)->get();
        $ord_items_arr = null;
        if (null != $ord_items && 0 < count($ord_items)) {
            $receiveDepotList = ReceiveDepot::getDeliveryWithReceiveDepotList(Event::order()->value, $sub_order_id, $delivery_id)->get();
            if (0 < count($receiveDepotList)) {
                $ord_items_arr = $ord_items->toArray();
                $receiveDepotList_arr = $receiveDepotList->toArray();
                foreach ($ord_items_arr as $ord_key => $ord_item) {
                    $ord_items_arr[$ord_key]->receive_depot = [];
                    foreach ($receiveDepotList_arr as $revd_key => $revd_item) {
                        if ($ord_items_arr[$ord_key]->product_style_id == $revd_item->product_style_id) {
                            array_push($ord_items_arr[$ord_key]->receive_depot, $receiveDepotList_arr[$revd_key]);
                            unset($receiveDepotList_arr[$revd_key]);
                        }
                    }
                }
            }
        }

        dd($ord_items_arr);

        return 'create'.$sub_order_id;
    }

    public function store(Request $request, int $sub_order_id)
    {
    }

    public function destroy(Request $request, int $sub_order_id)
    {
    }
}
