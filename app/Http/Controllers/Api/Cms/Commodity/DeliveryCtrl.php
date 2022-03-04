<?php

namespace App\Http\Controllers\Api\Cms\Commodity;

use App\Enums\Globals\ResponseParam;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\PurchaseInbound;
use App\Models\ReceiveDepot;
use Illuminate\Http\Request;

class DeliveryCtrl extends Controller
{
    //
    public static function getSelectInboundList(Request $request, $productStyleId)
    {
        $selectInboundList = PurchaseInbound::getSelectInboundList(['product_style_id' => $productStyleId])->get();

        $re = [];
        $re[ResponseParam::status()->key] = '0';
        $re[ResponseParam::msg()->key] = '';
        $re[ResponseParam::data()->key] = $selectInboundList->toArray();
        return response()->json($re);
    }

    public static function store(Request $request, $deliveryId, $itemId, $productStyleId = null) {
        $request->validate([
            'inbound_id.*' => 'nullable|integer|min:1',
            'qty.*' => 'nullable|integer|min:1',
        ]);
        $re = [];
        $input = $request->only('freebies', 'inbound_id', 'qty');
        if (count($input['inbound_id']) != count($input['qty'])) {
            return [ResponseParam::status()->key => 1, ResponseParam::msg()->key => '各資料個數不同'];
        }

//        //刪除子訂單商品的出貨資料
//        ReceiveDepot::where('delivery_id', '=', $deliveryId)
//            ->where('event_item_id', '=', $itemId)
//            ->delete();
        if (null != $input['qty'] && 0 < count($input['qty'])) {
            //取得request資料 重新建立該子訂單商品的出貨資料
            $re = ReceiveDepot::setDatasWithDeliveryIdWithItemId($input, $deliveryId, $itemId);
        }

        if ([] == $re) {
            $delivery = Delivery::where('id', '=', $deliveryId)->get()->first();
            $ord_items_arr = ReceiveDepot::getShipItemWithDeliveryWithReceiveDepotList($delivery->event, $delivery->event_id, $deliveryId, $productStyleId);
            $re[ResponseParam::status()->key] = 0;
            $re[ResponseParam::msg()->key] = '';
            $re[ResponseParam::data()->key] = $ord_items_arr;
        }
        return response()->json($re);
    }
}
