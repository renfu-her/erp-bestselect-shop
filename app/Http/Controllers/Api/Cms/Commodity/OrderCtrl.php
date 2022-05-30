<?php

namespace App\Http\Controllers\Api\Cms\Commodity;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderCtrl extends Controller
{
    //

    public function changeAutoDividend(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'order_id' => ['required'],
            'auto_dividend' => 'numeric|required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'message' => $validator->errors(),
            ]);
        }
        $d = $request->all();
        // dd($d);
        Order::where('id', $d['order_id'])->update(['auto_dividend' => $d['auto_dividend']]);

        return [
            'status' => '0',
        ];

    }

}
