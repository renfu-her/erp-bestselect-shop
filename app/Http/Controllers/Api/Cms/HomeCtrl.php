<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Template;
use Illuminate\Http\Request;

class HomeCtrl extends Controller
{
    //
    public function getBannerList(Request $request)
    {
        $dataList = Banner::getList()->orderBy('sort')->get();
        $re = [];
        $re['status'] = '0';
        $re['data'] = $dataList->toArray();
        //   $re['data'] = json_decode(json_encode($re['data']), true);
        return response()->json($re);
    }

    public function getTemplateList(Request $request)
    {
        $dataList = Template::getList()->orderBy('sort')->get();

        $re = [];
        $re['status'] = '0';
        $re['data'] = $dataList->toArray();
        return response()->json($re);
    }

}
