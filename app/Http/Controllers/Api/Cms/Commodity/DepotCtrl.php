<?php

namespace App\Http\Controllers\Api\Cms\Commodity;

use App\Enums\Globals\ResponseParam;
use App\Http\Controllers\Controller;
use App\Models\Depot;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\DepotProduct;

class DepotCtrl extends Controller
{
    public function get_select_product(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'send_depot_id' => 'exists:depot,id',
            'receive_depot_id' => 'exists:depot,id',
            'product_type' => 'string|in:c,p,all',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'message' => $validator->messages(),
            ]);
        }
        $type = $request->input('product_type', 'all'); //c,p,all

        $result = DepotProduct::productExistInboundList(request('send_depot_id'), request('receive_depot_id'), $type)
            ->paginate(10)->toArray();

        $result['status'] = '0';
        return response()->json($result);
    }


    public function get_select_csn_product(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'depot_id' => 'exists:depot,id',
            'product_type' => 'string|in:c,p,all',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'message' => $validator->messages(),
            ]);
        }
        $type = $request->input('product_type', 'all'); //c,p,all

        $result = DepotProduct::ProductCsnExistInboundList(request('depot_id'), $type)
            ->paginate(10)->toArray();

        $result['status'] = '0';
        return response()->json($result);
    }

    //給咖啡廳專案用
    //指定自取倉的商品庫存API
    //和get_select_csn_product，只差在回傳欄位不同
    public function get_csn_product(Request $request)
    {
        $query = $request->query();
        $validator = Validator::make($request->all(), [
            'depot_id' => 'required|exists:depot,id',
            'product_type' => 'filled|string|in:c,p,all',
            'page' => 'filled|numeric|min:1',
            'data_per_page' => 'filled|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'message' => $validator->messages(),
            ]);
        }
        $type = $request->input('product_type', 'all'); //c,p,all
        $data_per_page = Arr::get($query, 'data_per_page', 99999);

        $result = DepotProduct::ProductCsnExistInboundList(request('depot_id'), $type)
            ->select(
                'prd_list.sku as sku'
                ,'prd_list.product_title as name'
                ,'prd_list.spec as spec'
                ,'prd_list.depot_price as price'

                , DB::raw('ifnull(inbound.available_num, 0) as qty')
                , DB::raw('ifnull(inbound.prd_type, "") as prd_type')
            )
            ->paginate($data_per_page)->appends($query);

        $re[ResponseParam::status()->key] = '0';
        $re[ResponseParam::data()->key] = $result;
        return response()->json($re);
    }

    //給咖啡廳專案用
    //撈自取倉的API
    public static function get_pickup_depot(Request $request) {
        $re = Depot::getAllSelfPickup();
        return response()->json([
            ResponseParam::status()->key => '0',
            ResponseParam::data()->key => $re,
        ]);
    }
}
