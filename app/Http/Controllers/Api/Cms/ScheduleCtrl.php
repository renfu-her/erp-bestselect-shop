<?php

namespace App\Http\Controllers\Api\Cms;

use App\Enums\Discount\DividendCategory;
use App\Enums\Order\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerCoupon;
use App\Models\CustomerDividend;
use App\Models\Order;
use App\Models\OrderReportDaily;
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

    public function activeDividend(Request $request)
    {
        $order = Order::where('payment_status', OrderStatus::Received()->value)
            ->where('auto_dividend', '1')
            ->where('allotted_dividend', '0')
            ->where('dividend_active_at', '<=', now())
            ->get();

        foreach ($order as $ord) {
            CustomerDividend::activeDividend(DividendCategory::Order(), $ord->sn, now());
            CustomerCoupon::activeCoupon($ord->id, now());
        }

        return ['status' => '0'];
    }

    public function orderReportDaily()
    {
        OrderReportDaily::createData();
        return ['status' => '0'];
    }

}
