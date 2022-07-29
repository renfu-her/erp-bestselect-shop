<?php

namespace App\Http\Controllers\Api\Web;

use App\Enums\Globals\ApiStatusMessage;
use App\Enums\Globals\AppEnvClass;
use App\Enums\Globals\ImageDomain;
use App\Enums\Globals\ResponseParam;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

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
        $re[ResponseParam::status()->key] = ApiStatusMessage::Succeed;
        $re[ResponseParam::msg()->key] = ApiStatusMessage::getDescription(ApiStatusMessage::Succeed);
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

    public function getType1(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'collection_id' => 'required',
        ]);

        if ($validator->fails()) {
            $re = [];
            $re[ResponseParam::status()->key] = 'E01';
            $re[ResponseParam::msg()->key] = $validator->errors();

            return response()->json($re);
        }

        $d = $request->all();

        $dataList = Product::productList(null, null, [
            'img' => 1,
            'collection' => $d['collection_id'],
        ])->get()->toArray();

        Product::getMinPriceProducts(1, null, $dataList);

        $data = [];
        if ($dataList) {
            $data['name'] = $dataList[0]->collection_name;
            $data['list'] = array_map(function ($n) {
                if ($n->img_url) {
                    if (App::environment(AppEnvClass::Release ||
                        App::environment(AppEnvClass::Development))) {
                        $n->img_url =  ImageDomain::CDN . $n->img_url;
                    } else {
                        $n->img_url = asset($n->img_url);
                    }
                }else{
                    $n->img_url = '';
                }

                return $n;
            }, $dataList);
        }

        $re = [];
        $re[ResponseParam::status()->key] = '0';
        $re[ResponseParam::msg()->key] = '';
        $re[ResponseParam::data()->key] = $data;
        return response()->json($re);
    }

}
