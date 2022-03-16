<?php

namespace App\Http\Controllers\Api\Cms\Commodity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CollectionCtrl extends Controller
{
    
    public function getCollections(Request $request)
    {

        $validator = Validator::make($request->all(), [
               'supplier_id' => ['required'],
            
        ]);
        dd( $validator->messages());

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

        if (isset($d['consume'])) {
            $options['consume'] = $d['consume'];
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
}
