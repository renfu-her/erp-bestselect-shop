<?php

namespace App\Http\Controllers\Api\Web;

use App\Enums\Globals\ResponseParam;
use App\Enums\Received\ReceivedMethod;
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

    public function payinfo(Request $request)
    {

        $re = [];
        $re[ResponseParam::status()->key] = '0';
        $re[ResponseParam::msg()->key] = '';
        $re[ResponseParam::data()->key] = [
            ['id' => ReceivedMethod::Cash()->value,
                'name' => ReceivedMethod::Cash()->description],
            ['id' => ReceivedMethod::CreditCard()->value,
                'name' => ReceivedMethod::CreditCard()->description],
        ];
        return response()->json($re);

    }

}
