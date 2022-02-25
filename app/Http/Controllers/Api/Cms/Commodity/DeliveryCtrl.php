<?php

namespace App\Http\Controllers\Api\Cms\Commodity;

use App\Enums\Globals\ResponseParam;
use App\Http\Controllers\Controller;
use App\Models\PurchaseInbound;
use Illuminate\Http\Request;

class DeliveryCtrl extends Controller
{
    //
    public static function getSelectInboundList(Request $request, $product_style_id)
    {
        $selectInboundList = PurchaseInbound::getSelectInboundList(['product_style_id' => $product_style_id])->get();

        $re = [];
        $re[ResponseParam::status()->key] = '0';
        $re[ResponseParam::msg()->key] = '';
        $re[ResponseParam::data()->key] = $selectInboundList->toArray();
        return response()->json($re);
    }
}
