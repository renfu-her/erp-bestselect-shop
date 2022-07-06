<?php

namespace App\Http\Controllers\Api\Cms\Commodity;

use App\Enums\Discount\DividendCategory;
use App\Http\Controllers\Controller;
use App\Models\CustomerCoupon;
use App\Models\CustomerDividend;
use App\Models\Order;
use App\Models\OrderProfit;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

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

    public function updateProfit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profit_id' => ['required'],
            'bonus1' => ['required', 'numeric'],
            'bonus2' => ['numeric','nullable'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'message' => $validator->errors(),
            ]);
        }
        $d = $request->all();
        $bonus2 = Arr::get($d, 'bonus2', 0);

        $re = OrderProfit::updateProfit($d['profit_id'], $d['bonus1'], $bonus2);
        if ($re['success']) {
            return [
                'status' => '0',
            ];
        }

        return [
            'status' => '1',
            'message' => $re['message'],
        ];

    }

}
