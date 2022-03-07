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
        $product_style_id = $request->input('product_style_id')?? null;
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
            $re = ReceiveDepot::setDatasWithDeliveryIdWithItemId($input, $delivery_id, $item_id);
            if ($re['success'] == '1') {
                $delivery = Delivery::where('id', '=', $delivery_id)->get()->first();
                $ord_items_arr = ReceiveDepot::getShipItemWithDeliveryWithReceiveDepotList($delivery->event, $delivery->event_id, $delivery_id, $product_style_id);
                $re[ResponseParam::status()->key] = 0;
                $re[ResponseParam::msg()->key] = '';
                $re[ResponseParam::data()->key] = $ord_items_arr;
            }
        }

        if ([] == $re) {
            $re[ResponseParam::status()->key] = 1;
            $re[ResponseParam::msg()->key] = $re['error_msg'];
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
