<?php

namespace App\Http\Controllers\Api\Web;

use App\Enums\Globals\ResponseParam;
use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;

class OrderCtrl extends Controller
{
    //

    public function getGlobalDiscount(Request $request)
    {

        $dicount = Discount::getDiscounts('global-normal');
       
        $re = [];
        $re[ResponseParam::status()->key] = '0';
        $re[ResponseParam::msg()->key] = '';
        $re[ResponseParam::data()->key] = $dicount;
        return response()->json($re);

    }
}
