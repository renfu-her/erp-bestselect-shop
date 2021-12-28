<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Addr;
use Illuminate\Http\Request;

class StyleDemo extends Controller
{
    public function __invoke(Request $request)
    {
        $citys = Addr::getCitys();

        $regions = Addr::getRegions($citys[0]['city_id']);
        return view('cms.styleDemo', [
            'citys' => $citys,
            'regions' => $regions,
        ]);

    }
}
