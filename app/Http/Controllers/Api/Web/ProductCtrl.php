<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
class ProductCtrl extends Controller
{
    //

    public static function getSingleProduct(Request $request, $sku)
    {

        $re = Product::singleProduct($sku);

        if ($re) {
            return response()->json(['status' => 0, 'data' => $re]);
        } else {
            return response()->json(['status' => 'E04', 'msg' => '查無資料']);
        }

    }
}
