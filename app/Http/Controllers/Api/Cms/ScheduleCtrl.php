<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerDividend;
use Illuminate\Http\Request;

class ScheduleCtrl extends Controller
{
    //
    public function checkDividendExpired(Request $request)
    {
        foreach (Customer::get() as $customer) {
            CustomerDividend::checkExpired($customer->id);
        }

        return ['status' => '0'];
    }

    public function activeDividend(Request $request){

    }

}
