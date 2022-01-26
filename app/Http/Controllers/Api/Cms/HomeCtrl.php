<?php

namespace App\Http\Controllers\Api\Cms;

use App\Enums\Globals\ResponseParam;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Template;
use Illuminate\Http\Request;

class HomeCtrl extends Controller
{
    //
    public function getBannerList(Request $request)
    {
        $dataList = Banner::getListWithWeb(true)->orderBy('sort')->get();
        if (null != $dataList && 0 < count($dataList)) {
            foreach ($dataList as $key => $data) {
                if ($data->src != null) {
                    $dataList[$key]->src = asset($data->src);
                }
            }
        }
        $re = [];
        $re[ResponseParam::status()->key] = '0';
        $re[ResponseParam::msg()->key] = '';
        $re[ResponseParam::data()->key] = $dataList->toArray();
        //   $re['data'] = json_decode(json_encode($re['data']), true);
        return response()->json($re);
    }

    public function getTemplateList(Request $request)
    {
        $dataList = Template::getListWithWeb(true)->orderBy('sort')->get();

        $re = [];
        $re[ResponseParam::status()->key] = '0';
        $re[ResponseParam::msg()->key] = '';
        $re[ResponseParam::data()->key] = $dataList->toArray();
        return response()->json($re);
    }

}
