<?php

namespace App\Http\Controllers\Cms;

use App\Enums\Discount\DisMethod;
use App\Http\Controllers\Controller;
use App\Models\Addr;
use App\Models\Discount;
use Illuminate\Http\Request;

class DashboardCtrl extends Controller
{
    //
    public function __invoke(Request $request)
    {

        // Discount::createDiscount('a013', DisMethod::fromKey('cash'), 200, '2020/01/05', '2020/02/05', 1);
        $citys = Addr::getCitys();

        $regions = Addr::getRegions($citys[0]['city_id']);
        return view('cms.dashboard', [
            'citys' => $citys,
            'regions' => $regions,
        ]);

    }

}
