<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\OnePage;
use Illuminate\Http\Request;

class OnePageCtrl extends Controller
{
    public static function getPage(Request $request, $id)
    {

        $data = OnePage::where('id', $id)->where('active', 1)->get()->first();

        if (!$data) {
            return response()->json([
                'status' => '1',
                'msg' => '404',
            ]);
        }

        $data->products = OnePage::getProducts($data->collection_id, $data->sale_channel_id);

        return response()->json([
            'status' => '0',
            'data' => $data,
        ]);

    }

}
