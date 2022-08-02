<?php

namespace App\Http\Controllers\Api\Web;

use App\Enums\FrontEnd\CustomPageType;
use App\Enums\Globals\ApiStatusMessage;
use App\Http\Controllers\Controller;
use App\Models\CustomPages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomPagesCtrl extends Controller
{
    public function getData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => [
                'required',
                'string',
                'exists:App\Models\CustomPages,id',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => ApiStatusMessage::Fail,
                'msg' => $validator->errors(),
            ]);
        }

        $id = $request->input('id');
        $data = CustomPages::getDataListById($id);

        return response()->json([
            'status' => ApiStatusMessage::Succeed,
            'msg' => ApiStatusMessage::getDescription(ApiStatusMessage::Succeed),
            'data' => [
                'id' => $data->id,
                'content' => $data->content ?? '',
                'head' => $data->head ?? '',
                'body' => $data->body ?? '',
                'script' => $data->script ?? '',
                'page_name' => $data->page_name,
                'url' => CustomPages::getFullUrlPath($data->url, $data->id),
                'title' => $data->title,
                'desc' => $data->desc,
                'type' => CustomPageType::getDescription($data->type),
            ],
        ]);
    }
}
