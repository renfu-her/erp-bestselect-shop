<?php

namespace App\Http\Controllers\Api\Cms\Commodity;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DiscountCtrl extends Controller
{
    //
    public static function checkSn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sn' => 'required',
            //'title' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'message' => $validator->messages(),
            ]);
        }

        if (Discount::where('sn', $request->input('sn'))->get()->first()) {
            return response()->json([
                'status' => 'E02',
                'message' => '此序號已經被使用',
            ]);
        } else {
            return response()->json([
                'status' => '0',
                'message' => '可以使用',
            ]);
        }

    }

}
