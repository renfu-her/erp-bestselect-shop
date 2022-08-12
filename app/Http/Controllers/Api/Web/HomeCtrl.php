<?php

namespace App\Http\Controllers\Api\Web;

use App\Enums\Globals\ApiStatusMessage;
use App\Enums\Globals\AppEnvClass;
use App\Enums\Globals\ImageDomain;
use App\Enums\Globals\ResponseParam;
use App\Helpers\IttmsUtils;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
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

        $cond = [
            'img' => 1,
            'collection' => $d['collection_id'] ?? null,
        ];
        $dataList = Product::productList(null, null, $cond);
        $dataList = IttmsUtils::setPager($dataList, $request);
        $dataList = $dataList->get()->toArray();
        Product::getMinPriceProducts(1, null, $dataList);

        $data = [];
        if ($dataList) {
            $data['name'] = $dataList[0]->collection_name ?? null;
            $data['list'] = $this->getImgUrl($dataList);
        }

        $re = [];
        $re[ResponseParam::status()->key] = '0';
        $re[ResponseParam::msg()->key] = '';
        $re[ResponseParam::data()->key] = $data;
        return response()->json($re);
    }

    //店長推薦
    public function getRecommendCollectionList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_sku' => 'required|string',
            'page' => 'filled|numeric',
        ]);
        if ($validator->fails()) {
            $re = [];
            $re[ResponseParam::status()->key] = 'E01';
            $re[ResponseParam::msg()->key] = $validator->errors();

            return response()->json($re);
        }
        $d = $request->all();
        $product = Product::where('sku', '=', $d['product_sku'])->first();
        //找同群組商品
        $collection_1 = DB::table('collection_prd as collection_prd')
            ->where('collection_prd.collection_id_fk', '=', $product->recommend_collection_id)
            ->get();
        $product_id_fks = array_map(function ($n){
            return $n->product_id_fk;
        }, $collection_1->toArray());

        $cond = [
            'img' => 1,
            'public' => 1,
            'product_ids' => $product_id_fks ?? null,
        ];
        $dataList = Product::productList(null, null, $cond);
        $dataList = IttmsUtils::setPager($dataList, $request);
        $dataList = $dataList->get()->toArray();
        Product::getMinPriceProducts(1, null, $dataList);
        $data = $this->getImgUrl($dataList);

        $re = [];
        $re[ResponseParam::status()->key] = '0';
        $re[ResponseParam::msg()->key] = '';
        $re[ResponseParam::data()->key] = $data;
        return response()->json($re);
    }

    //同類商品
    public function getSameCategoryList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_sku' => 'required|string',
            'page' => 'filled|numeric',
        ]);
        if ($validator->fails()) {
            $re = [];
            $re[ResponseParam::status()->key] = 'E01';
            $re[ResponseParam::msg()->key] = $validator->errors();

            return response()->json($re);
        }
        $d = $request->all();
        $product = Product::where('sku', '=', $d['product_sku'])->first();

        //找同物流商品
        $prd_shipment_1 = DB::table('prd_product_shipment as prd_shipment')
            ->where('prd_shipment.product_id', '=', $product->id)
            ->get();
        $category_ids = array_map(function ($n){
            return $n->category_id;
        }, $prd_shipment_1->toArray());
        $group_ids = array_map(function ($n){
            return $n->group_id;
        }, $prd_shipment_1->toArray());
        $prd_shipments = DB::table('prd_product_shipment as prd_shipment')
            ->whereIn('prd_shipment.category_id', $category_ids)
            ->whereIn('prd_shipment.group_id', $group_ids)
            ->select('prd_shipment.product_id')
            ->get();
        $product_ids = array_map(function ($n){
            return $n->product_id;
        }, $prd_shipments->toArray());

        $cond = [
            'img' => 1,
            'public' => 1,
            'product_ids' => $product_ids ?? null,
        ];
        if(1 == $product->only_show_category) {
            //打勾 找同歸類
            $cond['category_id'] = $product->category_id ?? null;
        }
        $dataList = Product::productList(null, null, $cond);
        $dataList = IttmsUtils::setPager($dataList, $request);
        $dataList = $dataList->get()->toArray();
        Product::getMinPriceProducts(1, null, $dataList);
        $data = $this->getImgUrl($dataList);

        $re = [];
        $re[ResponseParam::status()->key] = '0';
        $re[ResponseParam::msg()->key] = '';
        $re[ResponseParam::data()->key] = $data;
        return response()->json($re);
    }

    private static function getImgUrl($dataList) {
        $result = $dataList;
        if ($dataList) {
            $result = array_map(function ($n) {
                if ($n->img_url) {
                    if (App::environment(AppEnvClass::Release) ||
                        App::environment(AppEnvClass::Development)) {
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
        return $result;
    }

}
