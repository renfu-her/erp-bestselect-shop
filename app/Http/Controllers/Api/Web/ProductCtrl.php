<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class ProductCtrl extends Controller
{
    //

    public static function getSingleProduct(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'sku' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'message' => $validator->messages(),
            ]);
        }
        $d = $request->all();
        $re = Product::singleProduct($d['sku']);

        if ($re) {
            return response()->json(['status' => 0, 'data' => $re]);
        } else {
            return response()->json(['status' => 'E04', 'msg' => '查無資料']);
        }

    }
}
