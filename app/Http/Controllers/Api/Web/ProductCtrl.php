<?php

namespace App\Http\Controllers\Api\Web;

use App\Enums\Globals\AppEnvClass;
use App\Enums\Globals\ImageDomain;
use App\Enums\Globals\ResponseParam;
use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class ProductCtrl extends Controller
{
    //

    public static function getSingleProduct(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'sku' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'message' => $validator->errors(),
            ]);
        }
        $d = $request->all();
        $re = Product::singleProduct($d['sku']);

        if ($re) {
            return response()->json(['status' => '0', 'data' => $re]);
        } else {
            return response()->json(['status' => 'E04', 'msg' => '查無資料']);
        }

    }

    public function getCollectionList(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'collection_id' => 'required',
            'type' => ['nullable', 'string', 'regex:/^1$/'],
        ]);

        if ($validator->fails()) {
            $re = [];
            $re[ResponseParam::status()->key] = 'E01';
            $re[ResponseParam::msg()->key] = $validator->errors();

            return response()->json($re);
        }

        $d = $request->all();

        $collection = Collection::where('id', $d['collection_id'])
            ->select(['id', 'name', 'meta_title', 'meta_description', 'url'])
            ->where('is_public', '1');

        if (isset($d['type'])) {
            if($d['type'] === '1') {
            $collection->where('is_liquor', '=', 1);
        }
        } else {
            $collection->where('is_liquor', '=', 0);
        }
        $collection = $collection->get()->first();

        if (!$collection) {
            $re = [];
            $re[ResponseParam::status()->key] = 'E02';
            $re[ResponseParam::msg()->key] = '查無此群組';

            return response()->json($re);
        }

        $sale_channel_id = 1;
        $dataList = Product::productList(null, null, [
            'price' => 1,
            'img' => 1,
            'collection' => $d['collection_id'],
            'public' => '1',
            'active_date' => '1',
            'online' => 'online',
            'sale_channel_id' => $sale_channel_id,
        ])->get()->toArray();

        if ($dataList) {
            $collection->list = array_map(function ($n) {
                if ($n->img_url) {
                    if (App::environment(AppEnvClass::Release)) {
                        $n->img_url = ImageDomain::CDN . $n->img_url;
                    } else {
                        $n->img_url = asset($n->img_url);
                    }
                }

                return $n;
            }, $dataList);

            Product::getMinPriceProducts($sale_channel_id, null, $dataList);

        }

        $re[ResponseParam::status()->key] = '0';
        $re[ResponseParam::msg()->key] = '';
        $re[ResponseParam::data()->key] = $collection;
        return response()->json($re);
    }

    /**
     * @param  Request  $request
     * 商品搜尋API controller
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchProductInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => ['required', 'string'],
            'sort.is_price_desc' => ['nullable', 'bool'],
            'page_size' => ['nullable', 'int', 'min:1'],
            'page' => ['nullable', 'int', 'min:1'],
            'm_class' => ['nullable', 'string', 'regex:/^(customer|employee|company)$/'],
        ]);



        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'msg' => $validator->errors(),
            ]);
        }

        return Product::searchProduct(
            $request['data'],
            $request['page_size'] ?? '',
            $request['page'] ?? 1,
            $request['sort']['is_price_desc'] ?? true
        );
    }
}
