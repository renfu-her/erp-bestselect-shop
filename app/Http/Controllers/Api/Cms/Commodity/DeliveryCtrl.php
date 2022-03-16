<?php

namespace App\Http\Controllers\Api\Cms\Commodity;

use App\Enums\Globals\ResponseParam;
use App\Http\Controllers\Controller;
use App\Models\PurchaseInbound;
use App\Models\ReceiveDepot;
use Illuminate\Http\Request;

class DeliveryCtrl extends Controller
{
    //
    public static function getSelectInboundList(Request $request)
    {
        $request->validate([
            'product_style_id' => 'required|int',
        ]);
        $product_style_id = $request->input('product_style_id');
        $selectInboundList = PurchaseInbound::getSelectInboundList(['product_style_id' => $product_style_id])->get();

        $re = [];
        $re[ResponseParam::status()->key] = '0';
        $re[ResponseParam::msg()->key] = '';
        $re[ResponseParam::data()->key] = $selectInboundList->toArray();
        return response()->json($re);
    }

    public static function store(Request $request) {
        $request->validate([
            'delivery_id' => 'required|int',
            'item_id' => 'required|int',
            'product_style_id' => 'filled|int',
            'inbound_id.*' => 'nullable|integer|min:1',
            'qty.*' => 'nullable|integer|min:1',
        ]);

        $delivery_id = $request->input('delivery_id')?? null;
        $item_id = $request->input('item_id')?? null;
        $re = [];
        $input = $request->only('freebies', 'inbound_id', 'qty');
        if (count($input['inbound_id']) != count($input['qty'])) {
            return [ResponseParam::status()->key => '1', ResponseParam::msg()->key => '各資料個數不同'];
        }

        if (null != $input['qty'] && 0 < count($input['qty'])) {
            //取得request資料 重新建立該子訂單商品的出貨資料
            $reRDSetDatas = ReceiveDepot::setDatasWithDeliveryIdWithItemId($input, $delivery_id, $item_id);
            if ($reRDSetDatas['success'] == '1') {
                $addIds = $reRDSetDatas['id'];
                $receiveDepotList = ReceiveDepot::whereIn('id', $addIds)->get();
                $re[ResponseParam::status()->key] = '0';
                $re[ResponseParam::msg()->key] = '';
                $re[ResponseParam::data()->key] = $receiveDepotList;
            } else {
                $re[ResponseParam::status()->key] = '1';
                $re[ResponseParam::msg()->key] = $reRDSetDatas['error_msg'];
                $re[ResponseParam::data()->key] = '';
            }
        }

        if ([] == $re) {
            $re[ResponseParam::status()->key] = '';
            $re[ResponseParam::msg()->key] = '';
        }
        return response()->json($re);
    }

    public function destroy(Request $request, int $receiveDepotId)
    {
        ReceiveDepot::deleteById($receiveDepotId);
        $re = [];
        $re[ResponseParam::status()->key] = '0';
        $re[ResponseParam::msg()->key] = '';
        return response()->json($re);
    }
}
