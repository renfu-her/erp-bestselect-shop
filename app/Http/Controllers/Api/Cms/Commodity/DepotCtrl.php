<?php

namespace App\Http\Controllers\Api\Cms\Commodity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        $type = request('product_type', 'all');//c,p,all

        $result = DepotProduct::productExistInboundList(request('send_depot_id'), request('receive_depot_id'), $type)
            ->paginate(10)->toArray();

        $result['status'] = '0';
        return response()->json($result);
    }
}
