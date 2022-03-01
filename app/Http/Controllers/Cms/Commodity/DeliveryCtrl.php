<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Delivery\Event;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
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

        $delivery_id1 = Delivery::createData(
            Event::order()->value
            , $sub_order->id
            , $sub_order->sn
            , $sub_order->ship_temp_id
            , $sub_order->ship_temp
            , $sub_order->ship_category
            , $sub_order->ship_category_name);

        $ord_items = DB::table('ord_items')->where('ord_items.sub_order_id', $sub_order_id)->get();
        dd($ord_items);
        return 'create'.$sub_order_id;
    }

    public function destroy(Request $request, int $sub_order_id)
    {
    }
}
