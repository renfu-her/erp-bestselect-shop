<?php

namespace App\Http\Controllers\Api\Web;

use App\Enums\Globals\ApiStatusMessage;
use App\Enums\Globals\ResponseParam;
use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductCtrl extends Controller
{
    //

    public static function getSingleProduct(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'sku' => 'required',
            'type' => ['nullable', 'string', 'regex:/^1$/'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'message' => $validator->errors(),
            ]);
        }
        $d = $request->all();
        if (isset($d['type'])) {
            if ($d['type'] === '1' &&
                !Product::isLiquor($d['sku'])
            ) {
                return response()->json([
                    'status' => ApiStatusMessage::NotFound,
                    'msg' => ApiStatusMessage::getDescription(ApiStatusMessage::NotFound),
                ]);
            }
        } else {
            if (Product::isLiquor($d['sku'])) {
                return response()->json([
                    'status' => ApiStatusMessage::NotFound,
                    'msg' => ApiStatusMessage::getDescription(ApiStatusMessage::NotFound),
                ]);
            }
        }
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
            if ($d['type'] === '1') {
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
        $dataList = DB::table('prd_products as product')
            ->select('product.id as id',
                'product.title as title',
                'product.sku as sku',
                'product.type as type',
                'product.consume as consume',
                'product.online as online',
                'product.offline as offline',
                'product.public as public')
            ->selectRaw('CASE product.type WHEN "p" THEN "一般商品" WHEN "c" THEN "組合包商品" END as type_title')
            ->where('product.public', 1)
            ->where('online', '1')
            ->where('product.online', 1)
            ->where(function ($query) {
                $now = date('Y-m-d H:i:s');
                $query->where('product.active_sdate', '<=', $now)
                    ->where('product.active_edate', '>=', $now)
                    ->orWhereNull('product.active_sdate')
                    ->orWhereNull('product.active_edate');
            })
            ->orderBy('id')
            ->whereNull('product.deleted_at');

        $subImg = DB::table('prd_product_images as img')
            ->select('img.url')
            ->whereRaw('img.product_id = product.id')
            ->limit(1);
        $dataList->addSelect(DB::raw("({$subImg->toSql()}) as img_url"));

        $dataList = $dataList->leftJoin('collection_prd as cprd', 'product.id', '=', 'cprd.product_id_fk')
                ->leftJoin('collection as colc', 'colc.id', '=', 'cprd.collection_id_fk')
                ->where('cprd.collection_id_fk', '=', $d['collection_id'])
                ->addSelect(['colc.name as collection_name', 'collection_id_fk'])
                ->get()
                ->toArray();

        if ($dataList) {
            $collection->list = array_map(function ($n) {
                if ($n->img_url) {
                    $n->img_url = getImageUrl($n->img_url, true);
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
            'type' => ['nullable', 'string', 'regex:/^1$/'],
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
            $request['sort']['is_price_desc'] ?? true,
            'customer',
            $request['type'] ?? '0',
        );
    }
}
