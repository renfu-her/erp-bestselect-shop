<?php

namespace App\Http\Controllers\Api\Cms\Commodity;

use App\Enums\Discount\DividendCategory;
use App\Http\Controllers\Controller;
use App\Models\CustomerDividend;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\CustomerCoupon;
class OrderCtrl extends Controller
{
    //

    public function changeAutoDividend(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'order_sn' => ['required'],
            'auto_dividend' => 'numeric|required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'message' => $validator->errors(),
            ]);
        }
        $d = $request->all();

        if (!Order::where('sn', $d['order_sn'])->get()->first()) {
            return response()->json([
                'status' => 'E02',
                'message' => "查無此單",
            ]);
        }

        Order::where('sn', $d['order_sn'])->update(['auto_dividend' => $d['auto_dividend']]);

        return [
            'status' => '0',
        ];

    }

    public function activeDividend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_sn' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'message' => $validator->errors(),
            ]);
        }
        $d = $request->all();
        $order = Order::where('sn', $d['order_sn'])->get()->first();
        if (!$order) {
            return response()->json([
                'status' => 'E02',
                'message' => "查無此單",
            ]);
        }

        CustomerDividend::activeDividend(DividendCategory::Order(), $d['order_sn']);
        CustomerCoupon::activeCoupon($order->id);

        return [
            'status' => '0',
        ];

    }

}
