<?php

namespace App\Http\Controllers\Api\Web;

use App\Enums\Globals\ApiStatusMessage;
use App\Enums\Globals\FrontendApiUrl;
use App\Enums\Globals\ResponseParam;
use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CollectionCtrl extends Controller
{
    public function collection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'string', 'regex:/^\d{1,}$/'],
//            'amount' => ['nullable', 'string', 'regex:/^\d{1,}$/']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 1,
                'msg' => $validator->errors()
            ]);
        }

        $id = $request->input('id');
//        $amount = $request->input('amount');

        $collectionName = Collection::where('id', $id)->get()->first()->name;
        $queryResult = Collection::getApiCollectionData($id);

        return response()->json([
            'status' => 0,
            'msg' => 'success',
            'data' => [
                'name' => $collectionName,
                'list' => $queryResult
            ]
        ]);
    }

    /**
     * @param  Request  $request
     *  取得特定類別的群組, type 1暫定酒類
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllCollection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => ['required', 'string', 'regex:/^1$/'],
        ]);

        if ($validator->fails()) {
            $re = [];
            $re[ResponseParam::status()->key] = ApiStatusMessage::Fail;
            $re[ResponseParam::msg()->key] = $validator->errors();

            return response()->json($re);
        }

        $req = $request->all();
        $collection = Collection::where('is_public', '1')
            ->where('is_liquor', '=', $req['type'])
            ->select([
                'id',
                'name',
                'url'
            ])
            ->get();

        if (!$collection) {
            $re = [];
            $re[ResponseParam::status()->key] = ApiStatusMessage::Fail;
            $re[ResponseParam::msg()->key] = '查無此群組';

            return response()->json($re);
        } else {
            return response()->json([
                'status' => ApiStatusMessage::Succeed,
                'msg'    => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
                'data' => $collection,
            ]);

        }

    }
}
