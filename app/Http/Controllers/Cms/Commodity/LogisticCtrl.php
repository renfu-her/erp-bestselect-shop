<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Delivery\Event;
use App\Enums\Delivery\LogisticStatus;
use App\Http\Controllers\Controller;
use App\Models\Consum;
use App\Models\Delivery;
use App\Models\Logistic;
use App\Models\LogisticFlow;
use App\Models\ShipmentGroup;
use App\Models\SubOrders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogisticCtrl extends Controller
{
    public function create($event, $eventId)
    {
        $delivery = null;
        $delivery_id = null;
        $returnAction = '';

        //顯示出貨商品列表product_title ; 單價price ; 數量send_qty ; 小計price*數量send_qty
        //組合包判斷兩者欄位不同都顯示:product_title rec_product_title，否則只顯示product_title
        $deliveryList = null;
        if (Event::order()->value == $event) {
            $sub_order = SubOrders::getListWithShiGroupById($eventId)->get()->first();
            if (null == $sub_order) {
                return abort(404);
            }
            $returnAction = Route('cms.order.detail', ['id' => $sub_order->order_id, 'subOrderId' => $eventId ]);

            // 出貨單號ID
            $delivery = Delivery::getData($event, $eventId)->get()->first();
            if (null != $delivery) {
                $delivery_id = $delivery->id;
            }
            $deliveryList = Delivery::getOrderListToLogistic($sub_order->order_id, $sub_order->id)->get();
        } else if (Event::consignment()->value == $event) {
            $returnAction = Route('cms.consignment.edit', ['id' => $eventId ]);

            // 出貨單號ID
            $delivery = Delivery::getData($event, $eventId)->get()->first();
            if (null != $delivery) {
                $delivery_id = $delivery->id;
            }
            $deliveryList = Delivery::getCsnListToLogistic($eventId)->get();
        }

        if (null == $delivery) {
            return abort(404);
        }
        $logistic = Logistic::where('delivery_id', $delivery_id)->get()->first();
        $logistic_id = null;
        //若沒有則新增
        if (null == $logistic) {
            $re = Logistic::createData($delivery_id);
            if ($re['success'] == 0) {
            } else {
                $logistic_id = $re['id'];
                $logistic = Logistic::where('id', $logistic_id)->get()->first();
            }
        } else {
            $logistic_id = $logistic->id;
        }

        //取得出貨耗材列表
        //打API post api/product/get-product-styles 帶參數 'consume':1

        //取得物流X成本列表
        $shipmentGroupWithCost = ShipmentGroup::getDataWithCost()->get();
        //取得耗材X入庫列表
        $consumWithInboundList = Consum::getConsumWithInboundList($logistic_id)->get()->toArray();

        foreach ($consumWithInboundList as $key => $value) {
            $consumWithInboundList[$key]->groupconcat = json_decode($value->groupconcat);
        }

        return view('cms.commodity.logistic.edit', [
            'returnAction' => $returnAction,
            'delivery' => $delivery,
            'logistic' => $logistic,
            'deliveryList' => $deliveryList,
            'shipmentGroup' => $shipmentGroupWithCost, //物流列表
            'consumWithInboundList' => $consumWithInboundList,
            'breadcrumb_data' => $logistic->sn
        ]);
    }

    //儲存物流相關資料
    public function store(Request $request)
    {
        $request->validate([
            'logistic_id' => 'required|numeric',
            'package_sn' => 'sometimes|nullable|string',
            'actual_ship_group_id' => 'required|numeric',
            'cost' => 'required|numeric|min:0',
            'memo' => 'sometimes|nullable|string',
        ]);
        $logistic_id = $request->input('logistic_id');
        $input = $request->only('logistic_id', 'actual_ship_group_id', 'cost', 'package_sn', 'memo');


        $errors = [];
        $logistic = Logistic::where('id', '=', $logistic_id)->get()->first();
        $delivery = Delivery::where('id', $logistic->delivery_id)->get()->first();
        //判斷若為子訂單 則回寫到子訂單資料表
        if (Event::order()->value == $delivery->event) {
            SubOrders::updateLogisticData($delivery->event_id
                , $input['package_sn']
                , $input['actual_ship_group_id']
                , $input['cost']
                , $input['memo']);
        }

        $reLgt = Logistic::updateData(
            $input['logistic_id']
            , $input['package_sn']
            , $input['actual_ship_group_id']
            , $input['cost']
            , $input['memo']
        );
        if ($reLgt['success'] == '0') {
            $errors['error_msg'] = $reLgt['error_msg'];
            return redirect()->back()->withInput()->withErrors($errors);
        }

        wToast('儲存成功');
        return redirect(Route('cms.logistic.create', [
            'event' => $delivery->event,
            'eventId' => $delivery->event_id
        ], true));
    }

    public static function storeConsum(Request $request) {
        $request->validate([
            'logistic_id' => 'required|int',
            'product_style_id' => 'filled|int',
            'inbound_id.*' => 'nullable|integer|min:1',
            'qty.*' => 'nullable|integer|min:1',
        ]);

        $logistic_id = $request->input('logistic_id')?? null;
        $errors = [];
        $input = $request->only('inbound_id', 'qty');
        if (count($input['inbound_id']) != count($input['qty'])) {
            $errors['error_msg'] = '各資料個數不同';
        }

        if (null != $input['qty'] && 0 < count($input['qty'])) {
            //取得request資料 重新建立該子訂單商品的出貨資料
            $reConsumSetData = Consum::setDatasWithLogisticId($input, $logistic_id);
            if ('1' != $reConsumSetData['success']) {
                $errors['error_msg'] = $reConsumSetData['error_msg'];
            }
        }
        if ([] != $errors) {
            return redirect()->back()->withInput()->withErrors($errors);
        } else {
            $logistic = Logistic::where('id', $logistic_id)->get()->first();
            $delivery = Delivery::where('id', $logistic->delivery_id)->get()->first();
            return redirect(Route('cms.logistic.create', [
                'event' => $delivery->event,
                'eventId' => $delivery->event_id
            ], true));
        }
    }

    //儲存耗材入庫，進行扣除入庫單
    public function auditInbound(Request $request) {
        $request->validate([
            'logistic_id' => 'required|numeric'
        ]);
        $logistic_id = $request->input('logistic_id');
        $errors = [];
        $logistic = Logistic::where('id', '=', $logistic_id)->get()->first();
        if (null != $logistic->audit_date) {
            $errors['error_msg'] = '不可重複送出審核';
        } else {
            $re = Consum::setUpLogisticData($logistic_id, $request->user()->id, $request->user()->name);
            if ($re['success'] == '1') {
                wToast('儲存成功');
                $delivery = Delivery::where('id', $logistic->delivery_id)->get()->first();
                return redirect(Route('cms.logistic.create', [
                    'event' => $delivery->event,
                    'eventId' => $delivery->event_id
                ], true));
            }
            $errors['error_msg'] = $re['error_msg'];
        }
        return redirect()->back()->withInput()->withErrors($errors);
    }

    //刪除物流單耗材
    public function destroyItem(Request $request, $event, $eventId, int $consumId)
    {
        Consum::deleteById($consumId);
        wToast('刪除成功');
        return redirect(Route('cms.logistic.create', [
            'event' => $event,
            'eventId' => $eventId], true));
    }

    //修改配送狀態
    public function changeLogisticStatus(Request $request, $event, $eventId) {
        $lastPageAction = '';
        $delivery_id = null;
        if (Event::order()->value == $event) {
            $delivery = Delivery::getDeliveryWithEventWithSn($event, $eventId)->get()->first();
            if (null != $delivery) {
                $delivery_id = $delivery->id;
                $subOrder = SubOrders::where('id', $eventId)->get()->first();
                $lastPageAction = Route('cms.order.detail', ['id' => $subOrder->order_id, 'subOrderId' => $eventId ]);
            }
        } else if (Event::consignment()->value == $event) {
            $delivery = Delivery::getDeliveryWithEventWithSn($event, $eventId)->get()->first();
            if (null != $delivery) {
                $delivery_id = $delivery->id;
                $lastPageAction = Route('cms.consignment.edit', ['id' => $eventId ]);
            }
        }
        $flowList = null;
        if (null != $delivery_id) {
            $flowList = LogisticFlow::getListByDeliveryId($delivery_id)->get();
        }

        return view('cms.commodity.logistic.change_status', [
            'lastPageAction' => $lastPageAction,
            'logisticStatus' => LogisticStatus::asArray(),
            'flowList' => $flowList,
            'event' => $event,
            'eventId' => $eventId,
            'delivery_id' => $delivery_id,
            'user' => $request->user(),
            'breadcrumb_data' => $delivery->sn
        ]);
    }

    public function updateLogisticStatus(Request $request, $event ,$eventId ,$deliveryId) {
        $request->validate([
            'statusCode.*' => 'required|string',
        ]);
        $statusCodes = $request->input('statusCode');
        $logistic_status_arr = [];
        //反轉送上來的順序再做儲存
        foreach ($statusCodes as $code) {
            try {
                $logistic_status = \App\Enums\Delivery\LogisticStatus::fromKey($code);
                array_push($logistic_status_arr, $logistic_status);
            } catch (\Exception $e) {
                wToast($e->getMessage());
                $errors['error_msg'] = $e->getMessage();
                return redirect()->back()->withInput()->withErrors($errors);
            }
        }

        $reLFCDS = LogisticFlow::createDeliveryStatus($request->user(), $deliveryId, $logistic_status_arr);
        if ($reLFCDS['success'] == 0) {
            wToast($reLFCDS['error_msg']);
        } else {
            wToast('新增成功');
        }

        return redirect(Route('cms.logistic.changeLogisticStatus', [
            'event' => $event,
            'eventId' => $eventId
        ], true));
    }
}

