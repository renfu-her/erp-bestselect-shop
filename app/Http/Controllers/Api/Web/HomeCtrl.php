<?php

namespace App\Http\Controllers\Api\Web;

use App\Enums\Globals\ApiStatusMessage;
use App\Enums\Globals\ResponseParam;
use App\Helpers\IttmsUtils;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use App\Models\ShipmentMethod;
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
            'public' => 1,
            'online' => $d['online'] ?? null,
            'collection' => $d['collection_id'] ?? null,
            'is_liquor' => 0,
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
        $product_id_fks = array_map(function ($n) {
            return $n->product_id_fk;
        }, $collection_1->toArray());

        $cond = [
            'img' => 1,
            'public' => 1,
            'online' => $d['online'] ?? null,
            'product_ids' => $product_id_fks ?? null,
            'collection' => 1,
            'is_liquor' => 0,
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
        $category_ids = array_map(function ($n) {
            return $n->category_id;
        }, $prd_shipment_1->toArray());
        $group_ids = array_map(function ($n) {
            return $n->group_id;
        }, $prd_shipment_1->toArray());
        $prd_shipments = DB::table('prd_product_shipment as prd_shipment')
            ->whereIn('prd_shipment.category_id', $category_ids)
            ->whereIn('prd_shipment.group_id', $group_ids)
            ->select('prd_shipment.product_id')
            ->get();
        $product_ids = array_map(function ($n) {
            return $n->product_id;
        }, $prd_shipments->toArray());

        $cond = [
            'img' => 1,
            'public' => 1,
            'online' => $d['online'] ?? null,
            'product_ids' => $product_ids ?? null,
            'collection' => 1,
            'is_liquor' => 0,
        ];
        if (1 == $product->only_show_category) {
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

    /**
     * @param  Request  $request
     *  用溫層ID、出貨方式ID查商品類別
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductCategoryByShipment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tmp_id' => [
                'required',
                'exists:shi_temps,id',
            ],
            'ship_id' => [
                'exists:shi_method,id',
            ],
            'page' => [
                'filled',
                'numeric',
            ],
        ]);
        if ($validator->fails()) {
            $resp = [];
            $resp[ResponseParam::status()->key] = ApiStatusMessage::Fail;
            $resp[ResponseParam::msg()->key] = $validator->errors();

            return response()->json($resp);
        }

        $req = $request->all();
        $shipMethodId = $req['ship_id'] ?? ShipmentMethod::findShipmentMethodIdByName('喜鴻出貨');
        $shiTempId = $req['tmp_id'];

        $dataList = DB::table('prd_product_shipment')
                        ->join('prd_products', function ($join){
                            $join->on('prd_product_shipment.product_id', '=', 'prd_products.id')
                                ->where('prd_products.public', '=', 1)
                                ->where('prd_products.online', '=', 1);
                        })
                        ->leftJoin('collection_prd', 'prd_products.id', '=', 'collection_prd.product_id_fk')
                        ->leftJoin('collection', 'collection_prd.collection_id_fk', '=', 'collection.id')
                        ->where('collection.is_liquor', '=', 0)
                        ->leftJoin('shi_group', 'prd_product_shipment.group_id', '=', 'shi_group.id')
                        ->where('shi_group.method_fk', '=', $shipMethodId)
                        ->leftJoin('shi_temps', 'shi_group.temps_fk', '=', 'shi_temps.id')
                        ->where('shi_temps.id', $shiTempId)
                        ->leftJoin('prd_categorys', 'prd_products.category_id', '=', 'prd_categorys.id')
                        ->select([
                            'prd_categorys.id AS category_id',
                            'prd_categorys.category',
                        ])
                        ->groupBy('category_id')
                        ->orderBy('category_id');

        $dataList = IttmsUtils::setPager($dataList, $request);
        $dataList = $dataList->get()->toArray();

        return response()->json([
            'status' => ApiStatusMessage::Succeed,
            'msg' => '',
            'data' => $dataList,
        ]);
    }

    /**
     * @param  Request  $request
     * 用溫層ID、產品歸類ID、出貨方式ID查詢商品列表
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function getProductListByShipment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tmp_id' => [
                'required',
                'exists:shi_temps,id',
            ],
            'category_id' => [
                'required',
                'exists:prd_categorys,id',
            ],
            'ship_id' => [
                'exists:shi_method,id',
            ],
            'page' => [
                'filled',
                'numeric',
            ],
        ]);
        if ($validator->fails()) {
            $resp = [];
            $resp[ResponseParam::status()->key] = ApiStatusMessage::Fail;
            $resp[ResponseParam::msg()->key] = $validator->errors();

            return response()->json($resp);
        }

        $req = $request->all();
        $shiTempId = $req['tmp_id'];
        $categoryId = $req['category_id'];
        $shipMethodId = $req['ship_id'] ?? ShipmentMethod::findShipmentMethodIdByName('喜鴻出貨');

        //找同物流商品
        $prd_shipment_1 = DB::table('prd_product_shipment as prd_shipment')
            ->join('prd_products', function ($join){
                $join->on('prd_shipment.product_id', '=', 'prd_products.id');
            })
            ->leftJoin('collection_prd', 'prd_products.id', '=', 'collection_prd.product_id_fk')
            ->leftJoin('collection', 'collection_prd.collection_id_fk', '=', 'collection.id')
            ->get();

        $category_ids = array_map(function ($n) {
            return $n->category_id;
        }, $prd_shipment_1->toArray());
        $group_ids = array_map(function ($n) {
            return $n->group_id;
        }, $prd_shipment_1->toArray());
        $product_ids = array_map(function ($n) {
            return $n->product_id;
        }, $prd_shipment_1->toArray());
        $prd_shipments = DB::table('prd_product_shipment as prd_shipment')
            ->whereIn('prd_shipment.category_id', $category_ids)
            ->whereIn('prd_shipment.group_id', $group_ids)
            ->whereIn('prd_shipment.product_id', $product_ids)
            ->select('prd_shipment.product_id')
            ->get();
        $product_ids = array_map(function ($n) {
            return $n->product_id;
        }, $prd_shipments->toArray());

        $cond = [
            'img' => 1,
            'public' => 1,
            'online' => 1,
            'product_ids' => $product_ids ?? null,
            'collection' => 1,
            'is_liquor' => 0,
        ];

        $dataList = Product::productList(null, null, $cond);
        $dataList->join('prd_product_shipment', 'product.id', '=', 'prd_product_shipment.product_id')
                ->leftJoin('shi_group', 'prd_product_shipment.group_id', '=', 'shi_group.id')
                ->where('shi_group.method_fk', '=', $shipMethodId)
                ->leftJoin('shi_temps', 'shi_group.temps_fk', '=', 'shi_temps.id')
                ->where('shi_temps.id', '=', $shiTempId)
                ->leftJoin('prd_categorys', 'product.category_id', '=', 'prd_categorys.id')
                ->where('prd_categorys.id', '=', $categoryId)
                ->addSelect([
                    'shi_temps.temps',
                    'prd_categorys.category',
                ]);

        $dataList = IttmsUtils::setPager($dataList, $request);
        $dataList = $dataList->get()->toArray();
        Product::getMinPriceProducts(1, null, $dataList);
        $data = $this->getImgUrl($dataList);

        return response()->json([
            'status' => ApiStatusMessage::Succeed,
            'msg' => '',
            'data' => $data,
        ]);
    }
    private static function getImgUrl($dataList)
    {
        $result = $dataList;
        if ($dataList) {
            $result = array_map(function ($n) {
                if ($n->img_url) {
                    $n->img_url = getImageUrl($n->img_url, true);
                } else {
                    $n->img_url = '';
                }

                return $n;
            }, $dataList);
        }
        return $result;
    }

}
