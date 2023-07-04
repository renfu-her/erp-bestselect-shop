<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\OnePage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OnePageCtrl extends Controller
{

    function list(Request $request) {
        $data = OnePage::select(['id', 'title', 'img'])->where('active', 1)
            ->where('app', 1)->get()->toArray();
    
        $data = array_map(function ($n) {
            $n['url'] = env('FRONTEND_URL') . "store/" . $n['id'];
            return $n;
        }, $data);

        return response()->json([
            'status' => '0',
            'data' => $data,
        ]);

    }

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

    public function getUrl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country' => 'required',
            'account' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'errors' => $validator->errors()], 400);
        }

        $re = $request->all();

        $customer = DB::table('usr_users as user')
            ->join('usr_customers as customer', 'user.customer_id', '=', 'customer.id')
            ->select('customer.sn')
            ->where('user.account', $re['account'])
            ->get()->first();

        if (!$customer) {
            return response()->json([
                'status' => 'E02',
                'errors' => '查無此用戶'], 400);
        }

        $onepage = OnePage::where('country', $re['country'])
            ->where('active', 1)
            ->get()->first();

        if (!$onepage) {
            return response()->json([
                'status' => 'E01',
                'errors' => '查無頁面'], 400);
        }

        $url = frontendUrl() . 'store/' . $onepage->id . '?openExternalBrowser=1&mcode=' . $customer->sn;
        return response()->json(['status' => '0',
            'data' => $url]);
    }

}
