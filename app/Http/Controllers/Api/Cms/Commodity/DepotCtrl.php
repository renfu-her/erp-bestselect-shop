<?php

namespace App\Http\Controllers\Api\Cms\Commodity;

use App\Http\Controllers\Controller;

use App\Models\PurchaseInbound;
use Illuminate\Http\Request;

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
        $query = $request->query();

        $type = request('product_type', 'all');//c,p,all

        $extPrdStyle = [];
        $extPrdStyleList = PurchaseInbound::getExistInboundProductStyleList(request('send_depot_id'))->get()->toArray();
        foreach ($extPrdStyleList as $prdStyle) {
            array_push($extPrdStyle, $prdStyle->product_style_id);
        }

        $re = DepotProduct::product_list(request('receive_depot_id'), null, $type)
            ->orderBy('product_id', 'ASC')
            ->orderBy('id', 'ASC')
            ->select('style.id', 'style.sku', 'product.title as product_title', 'product.id as product_id', 'style.title as spec', DB::raw('CASE product.type WHEN "p" THEN "一般商品" WHEN "c" THEN "組合包商品" END as type_title'), 'style.in_stock'
                , 'select_list.depot_price'
                , 'product.type'
                , DB::raw('CASE product.type WHEN "p" THEN "一般商品" WHEN "c" THEN "組合包商品" END as type_title')
            )
            ->whereIn('style.id', $extPrdStyle)
            ->paginate(10)->toArray();

        $re['status'] = '0';

        return response()->json($re);
    }
}
