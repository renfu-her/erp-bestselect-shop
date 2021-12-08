<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Addr;
use Illuminate\Http\Request;

class DashboardCtrl extends Controller
{
    //
    public function __invoke(Request $request)
    {
        $citys = Addr::getCitys();

        $regions = Addr::getRegions($citys[0]['city_id']);
        return view('cms.dashboard', [
            'citys' => $citys,
            'regions' => $regions,
        ]);

    }

}
