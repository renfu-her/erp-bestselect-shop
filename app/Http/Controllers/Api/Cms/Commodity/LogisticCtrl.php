<?php

namespace App\Http\Controllers\Api\Cms\Commodity;

use App\Enums\Globals\ResponseParam;
use App\Http\Controllers\Controller;
use App\Models\Consum;
use Illuminate\Http\Request;

class LogisticCtrl extends Controller
{
    public static function store(Request $request) {
        $request->validate([
            'logistic_id' => 'required|int',
            'product_style_id' => 'filled|int',
            'inbound_id.*' => 'nullable|integer|min:1',
            'qty.*' => 'nullable|integer|min:1',
        ]);

        $logistic_id = $request->input('logistic_id')?? null;
        $re = [];
        $input = $request->only('inbound_id', 'qty');
        if (count($input['inbound_id']) != count($input['qty'])) {
            return [ResponseParam::status()->key => '1', ResponseParam::msg()->key => '各資料個數不同'];
        }

        if (null != $input['qty'] && 0 < count($input['qty'])) {
            //取得request資料 重新建立該子訂單商品的出貨資料
            $reConsumSetData = Consum::setDatasWithLogisticId($input, $logistic_id);
            if ($reConsumSetData['success'] == '1') {
                $addIds = $reConsumSetData['id'];
                $receiveDepotList = Consum::whereIn('id', $addIds)->get();
                $re[ResponseParam::status()->key] = '0';
                $re[ResponseParam::msg()->key] = '';
                $re[ResponseParam::data()->key] = $receiveDepotList;
            } else {
                $re[ResponseParam::status()->key] = '1';
                $re[ResponseParam::msg()->key] = $reConsumSetData['error_msg'];
                $re[ResponseParam::data()->key] = '';
            }
        }

        if ([] == $re) {
            $re[ResponseParam::status()->key] = '';
            $re[ResponseParam::msg()->key] = '';
        }
        return response()->json($re);
    }
}
