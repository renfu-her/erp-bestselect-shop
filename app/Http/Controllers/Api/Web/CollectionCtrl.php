<?php

namespace App\Http\Controllers\Api\Web;

use App\Enums\Globals\FrontendApiUrl;
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
}
