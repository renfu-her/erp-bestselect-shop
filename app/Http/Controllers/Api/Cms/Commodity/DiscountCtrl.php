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

    public static function changeActive(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'active' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'message' => $validator->messages(),
            ]);
        }
        Discount::where('id', $request->input('id'))->update(['active' => $request->input('active')]);

        return response()->json([
            'status' => '0',
        ]);

    }

    public static function getNormalDiscount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'message' => $validator->messages(),
            ]);
        }

        return response()->json([
            'status' => '0',
            'data' => Discount::getDiscounts('non-global-normal', $request->input('product_id')),
        ]);

    }

}
