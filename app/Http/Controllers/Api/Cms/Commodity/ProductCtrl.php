<?php

namespace App\Http\Controllers\Api\Cms\Commodity;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class ProductCtrl extends Controller
{
    //
    public function getProductStyles(Request $request)
    {

        $validator = Validator::make($request->all(), [
            //   'supplier_id' => ['required'],
            'price' => 'numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'message' => $validator->messages(),
            ]);
        }
        $d = $request->all();
        $options = [];
        if (isset($d['price'])) {
            $options['price'] = $d['price'];
        }
        if (isset($d['supplier_id'])) {
            $options['supplier'] = ['condition' => $d['supplier_id']];
        }

        // Arr::get($d, 'supplier_id',''),

        $re = Product::productStyleList(
            Arr::get($d, 'keyword', ''),
            Arr::get($d, 'type', ''),
            [],
            $options,

        )->paginate(10)->toArray();
        $re['status'] = '0';
        //   $re['data'] = json_decode(json_encode($re['data']), true);
        return response()->json($re);
    }

    public function getProductList(Request $request)
    {

        $validator = Validator::make($request->all(), [
            //   'supplier_id' => ['required'],
            //'title' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'message' => $validator->messages(),
            ]);
        }

        $d = $request->all();

        $re = Product::productList(
            Arr::get($d, 'title', ''),
            Arr::get($d, 'id', ''),
            Arr::get($d, 'options', ''),
        )->paginate(10)->toArray();
        $re['status'] = '0';
        //   $re['data'] = json_decode(json_encode($re['data']), true);
        return response()->json($re);
    }

    public function getProductShipment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => ['required'],

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'message' => $validator->messages(),
            ]);
        }
        $re = Product::getProductShipments($request->input('product_id'));
        if (count($re) == 0) {
            return response()->json(['status' => 'empty', 'message' => '無資料']);
        }
        return response()->json(['status' => '0', 'data' => $re]);
    }

    

    // Product::getProductShipments($id);

}
