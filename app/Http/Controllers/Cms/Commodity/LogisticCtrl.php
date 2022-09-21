<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Delivery\Event;
use App\Enums\Delivery\LogisticStatus;
use App\Http\Controllers\Controller;
use App\Models\Consignment;
use App\Models\ConsignmentItem;
use App\Models\Consum;
use App\Models\CsnOrder;
use App\Models\Delivery;
use App\Models\Depot;
use App\Models\Logistic;
use App\Models\LogisticFlow;
use App\Models\LogisticProjLogisticLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShipmentGroup;
use App\Models\SubOrders;
use App\Models\User;
use App\Models\UserProjLogistics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LogisticCtrl extends Controller
{
    public function create(Request $request, $event, $eventId)
    {
        $rsp_arr = [];

        $delivery = null;
        $delivery_id = null;
        $returnAction = '';
        $event_sn = '';

        //顯示出貨商品列表product_title ; 單價price ; 數量send_qty ; 小計price*數量send_qty
        //組合包判斷兩者欄位不同都顯示:product_title rec_product_title，否則只顯示product_title
        $deliveryList = null;
        if (Event::order()->value == $event) {
            $sub_order = SubOrders::getListWithShiGroupById($eventId)->get()->first();
            if (null == $sub_order) {
                return abort(404);
            }
            $event_sn = $sub_order->sn;
            $returnAction = Route('cms.order.detail', ['id' => $sub_order->order_id, 'subOrderId' => $eventId ]);

            // 出貨單號ID
            $delivery = Delivery::getData($event, $eventId)->get()->first();
            if (null != $delivery) {
                $delivery_id = $delivery->id;
            }
            $deliveryList = Delivery::getOrderListToLogistic($delivery_id, $sub_order->order_id, $sub_order->id)->get();
        } else if (Event::consignment()->value == $event) {
            $returnAction = Route('cms.consignment.edit', ['id' => $eventId ]);

            // 出貨單號ID
            $delivery = Delivery::getData($event, $eventId)->get()->first();
            if (null != $delivery) {
                $delivery_id = $delivery->id;
            }
            $deliveryList = Delivery::getCsnListToLogistic($delivery_id, $eventId)->get();

            $consignment = Consignment::where('id', $delivery->event_id)->get()->first();
            $event_sn = $consignment->sn;
            $rsp_arr['depot_id'] = $consignment->send_depot_id;
        } else if (Event::csn_order()->value == $event) {
            $returnAction = Route('cms.consignment-order.edit', ['id' => $eventId ]);

            // 出貨單號ID
            $delivery = Delivery::getData($event, $eventId)->get()->first();
            if (null != $delivery) {
                $delivery_id = $delivery->id;
            }
            $deliveryList = Delivery::getCsnOrderListToLogistic($delivery_id, $eventId)->get();
            $csnOrder = CsnOrder::where('id', $delivery->event_id)->get()->first();
            $event_sn = $csnOrder->sn;
            $rsp_arr['depot_id'] = $csnOrder->depot_id;
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

        $depots = null;
        $temps = null;
        $dims = null;
        $send_name = ''; $send_tel = ''; $send_addr = '';
        $rcv_name = ''; $rcv_tel = ''; $rcv_addr = '';
        $items = null;
        if (false == isset($logistic->projlgt_order_sn)) {
            $logisticUserApiToken = User::getLogisticApiToken($request->user()->id)->user_token;
            if (false == empty($logisticUserApiToken)) {
                $api_depot = UserProjLogistics::getDepot($logisticUserApiToken);
                if ($api_depot['success'] == 0) {
                    wToast('取得倉庫列表錯誤 '. $api_depot['error_msg']);
                } else {
                    $depots = $api_depot['data'];
                }
                $api_temp = UserProjLogistics::getTemp($logisticUserApiToken);
                if ($api_temp['success'] == 0) {
                    wToast('取得溫層列表錯誤 '. $api_temp['error_msg']);
                } else {
                    $temps = $api_temp['data'];

                    $api_dim = UserProjLogistics::getDim($logisticUserApiToken, $temps[0]->id);
                    if ($api_dim['success'] == 0) {
                        wToast('取得溫層列表錯誤 '. $api_dim['error_msg']);
                    } else {
                        $dims = $api_dim['data'];
                    }
                }
                list($send_name, $send_tel, $send_addr, $rcv_name, $rcv_tel, $rcv_addr, $memo, $items) =
                    $this->getDataProjLogisticCreateOrder($event, $delivery, $send_name, $send_tel, $send_addr, $rcv_name, $rcv_tel, $rcv_addr, $items);
            }
        }
        $projLogisticLog = LogisticProjLogisticLog::getDataWithLogisticId($logistic_id);

        $rsp_arr['returnAction'] = $returnAction;
        $rsp_arr['delivery'] = $delivery;
        $rsp_arr['logistic'] = $logistic;
        $rsp_arr['deliveryList'] = $deliveryList;
        $rsp_arr['shipmentGroup'] = $shipmentGroupWithCost;
        $rsp_arr['consumWithInboundList'] = $consumWithInboundList;
        $rsp_arr['event'] = $event;
        $rsp_arr['depots'] = $depots;
        $rsp_arr['temps'] = $temps;
        $rsp_arr['dims'] = $dims;
        $rsp_arr['send_name'] = $send_name;
        $rsp_arr['projLogisticLog'] = $projLogisticLog;
        $rsp_arr['DelLogisticOrderAction'] = Route('cms.logistic.deleteLogisticOrder');
        $rsp_arr['breadcrumb_data'] = ['sn' => $event_sn, 'parent' => $event ];
        return view('cms.commodity.logistic.edit', $rsp_arr);
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


    //儲存耗材入庫，退回
    public function auditReturnInbound(Request $request) {
        $request->validate([
            'logistic_id' => 'required|numeric'
        ]);
        $logistic_id = $request->input('logistic_id');
        $errors = [];
        $logistic = Logistic::where('id', '=', $logistic_id)->get()->first();
        if (null != $logistic->audit_date) {
            $re = Consum::setDownLogisticData($logistic_id, $request->user()->id, $request->user()->name);
            if ($re['success'] == '1') {
                wToast('取消成功');
                $delivery = Delivery::where('id', $logistic->delivery_id)->get()->first();
                return redirect(Route('cms.logistic.create', [
                    'event' => $delivery->event,
                    'eventId' => $delivery->event_id
                ], true));
            }
            $errors['error_msg'] = $re['error_msg'];
        } else {
            $errors['error_msg'] = '尚未審核過 無法退回';
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
        $event_sn = '';
        if (Event::order()->value == $event) {
            $delivery = Delivery::getDeliveryWithEventWithSn($event, $eventId)->get()->first();
            if (null != $delivery) {
                $delivery_id = $delivery->id;
                $sub_order = SubOrders::where('id', $eventId)->get()->first();
                $event_sn = $sub_order->sn;
                $lastPageAction = Route('cms.order.detail', ['id' => $sub_order->order_id, 'subOrderId' => $eventId ]);
            }
        } else if (Event::consignment()->value == $event) {
            $delivery = Delivery::getDeliveryWithEventWithSn($event, $eventId)->get()->first();
            if (null != $delivery) {
                $delivery_id = $delivery->id;
                $consignment = Consignment::where('id', $eventId)->get()->first();
                $event_sn = $consignment->sn;
                $lastPageAction = Route('cms.consignment.edit', ['id' => $eventId ]);
            }
        } else if (Event::csn_order()->value == $event) {
            $delivery = Delivery::getDeliveryWithEventWithSn($event, $eventId)->get()->first();
            if (null != $delivery) {
                $delivery_id = $delivery->id;
                $csn_order = CsnOrder::where('id', $eventId)->get()->first();
                $event_sn = $csn_order->sn;
                $lastPageAction = Route('cms.consignment-order.edit', ['id' => $eventId ]);
            }
        }
        $flowList = null;
        if (null != $delivery_id) {
            $flowList = LogisticFlow::getListByDeliveryId($delivery_id)->get();
        }

        $logisticStatus = LogisticStatus::asArray();
        if ($event != Event::csn_order()->value) {
            unset($logisticStatus[LogisticStatus::D9000()->key]);
        }

        return view('cms.commodity.logistic.change_status', [
            'lastPageAction' => $lastPageAction,
            'logisticStatus' => $logisticStatus,
            'flowList' => $flowList,
            'event' => $event,
            'eventId' => $eventId,
            'delivery_id' => $delivery_id,
            'user' => $request->user(),
            'breadcrumb_data' => ['sn' => $event_sn, 'parent' => $event ],
        ]);
    }

    public function updateLogisticStatus(Request $request, $event ,$eventId ,$deliveryId) {
        $request->validate([
            'statusCode.*' => 'required|string',
        ]);
        $statusCodes = $request->input('statusCode', []);
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

    public function createLogisticOrder(Request $request) {
        $request->validate([
            'is_true_sender' => ['required', 'string', 'regex:/^(0|1)$/'],
            'depot_id' => 'required|string',
            'temp_id' => 'required|string',
            'dim_id' => 'required|string',
            'pickup_date' => 'required|date',
            'delivery_id' => 'required|string',
            'logistic_id' => 'required|string',
            'event' => 'required|string',
            'event_id' => 'required|string',
        ]);

        $input = $request->only('is_true_sender', 'depot_id', 'temp_id', 'dim_id', 'pickup_date', 'memo');
        $pickup_date = date('Y/m/d', strtotime($input['pickup_date']));

        $logistic_id = $request->input('logistic_id');
        $delivery_id = $request->input('delivery_id');
        $event = $request->input('event');
        $eventId = $request->input('event_id');

        $delivery = Delivery::where('id', $delivery_id)->get()->first();
        $order_no = $delivery->event_sn;
        $send_name = ''; $send_tel = ''; $send_addr = '';
        $rcv_name = ''; $rcv_tel = ''; $rcv_addr = '';
        $items = null;
        list($send_name, $send_tel, $send_addr, $rcv_name, $rcv_tel, $rcv_addr, $memo, $items) =
            $this->getDataProjLogisticCreateOrder($event, $delivery, $send_name, $send_tel, $send_addr, $rcv_name, $rcv_tel, $rcv_addr, $items);
        if (isset($input['memo'])) {
            $memo = $memo. ' '. $input['memo'] ?? '';
        }
        //真實寄件人帶值 其餘不傳此欄位
        if ('0' == $input['is_true_sender']) {
            $send_name = '';
            $send_tel = '';
            $send_addr = '';
        }

        $logisticUserApiToken = User::getLogisticApiToken($request->user()->id)->user_token;
        $createOrder = UserProjLogistics::createOrder($request->user(), $logistic_id, $logisticUserApiToken
            , $input['depot_id'], $input['temp_id'], $input['dim_id']
            , $rcv_name, $rcv_tel, $rcv_addr
            , $memo, $order_no, $pickup_date
            , $items
            , $send_name, $send_tel, $send_addr
        );
        if ($createOrder['success'] == 0) {
            throw ValidationException::withMessages(['createOrder' => json_encode($createOrder['error_msg'])]);
        } else {
            DB::beginTransaction();
            Logistic::updateProjlgtOrderSn($logistic_id, $createOrder['sn'], $delivery->event, $delivery->event_id);
            $reLFCDS = LogisticFlow::createDeliveryStatus($request->user(), $delivery->id, [LogisticStatus::A4000()]);
            if ($reLFCDS['success'] == 0) {
                DB::rollBack();
                return $reLFCDS;
            }
            DB::commit();
            wToast('新增託運單成功');
        }

        return redirect(Route('cms.logistic.create', [
            'event' => $event,
            'eventId' => $eventId], true));
    }

    public function deleteLogisticOrder(Request $request) {
        $request->validate([
            'event' => 'required|string',
            'event_id' => 'required|string',
            'logistic_id' => 'required|string',
            'sn' => 'required|string',
        ]);
        $sn = $request->input('sn');
        $event = $request->input('event');
        $eventId = $request->input('event_id');
        $logisticId = $request->input('logistic_id');

        $logistic = Logistic::where('id', $logisticId)->get()->first();
        if (null == $logistic) {
            return abort(404);
        }

        $logisticUserApiToken = User::getLogisticApiToken($request->user()->id)->user_token;
        $delSn = UserProjLogistics::delSn($request->user(), $logistic->id, $logisticUserApiToken, $sn);
        if ($delSn['success'] == 0) {
            throw ValidationException::withMessages(['sn' => $delSn['error_msg']]);
        } else {
            Logistic::updateProjlgtOrderSn($logisticId, null, $event, $eventId);
            wToast('刪除託運單成功');
        }
        return redirect(Route('cms.logistic.create', [
            'event' => $event,
            'eventId' => $eventId], true));
    }

    private function getDataProjLogisticCreateOrder($event, $delivery
        , $send_name, $send_tel, $send_addr, $rcv_name, $rcv_tel, $rcv_addr
        , $items): array
    {
        $memo = null;
        if (Event::order()->value == $event) {
            $suborder = SubOrders::where('id', '=', $delivery->event_id)->get()->first();
            $orderQuery = DB::table('ord_orders as order')
                ->where('order.id', '=', $suborder->order_id)
                ->select('order.id as order_id');
            Order::orderAddress($orderQuery);
            $order = $orderQuery->get()->first();
            $send_name = $order->sed_name;
            $send_tel = $order->sed_phone;
            $send_addr = $order->sed_address;

            if ('pickup' == $suborder->ship_category) {
                //自取，還是要從理貨倉出貨到門市，所以收件地是門市
                $pickup = Product::getPickupWithPickUpId($suborder->ship_event_id)->get()->first();
                $rcv_name = $pickup->depot_name;
                $rcv_tel = $pickup->depot_tel;
                $rcv_addr = $pickup->depot_address;
            } else {
                $rcv_name = $order->rec_name;
                $rcv_tel = $order->rec_phone;
                $rcv_addr = $order->rec_address;
            }
            $items = OrderItem::where('order_id', '=', $suborder->order_id)
                ->select('id as item_id'
                    , 'product_title as title'
                    , 'qty as qty'
                    , 'discounted_price as price'
                    , DB::raw('(qty * discounted_price) as subtotal')
                )
                ->where('sub_order_id', '=', $suborder->id)
                ->get()->toArray();
        } else if (Event::consignment()->value == $event) {
            $consignment = Consignment::where('id', '=', $delivery->event_id)->get()->first();
            $send_depot = Depot::where('id', $consignment->send_depot_id)->get()->first();
            $send_name = $send_depot->name;
            $send_tel = $send_depot->tel;
            $send_addr = $send_depot->address;

            $receive_depot = Depot::where('id', $consignment->receive_depot_id)->get()->first();
            $rcv_name = $receive_depot->name;
            $rcv_tel = $receive_depot->tel;
            $rcv_addr = $receive_depot->address;
            $memo = $consignment->memo;

            $consignment_item = ConsignmentItem::getProjLogisticItemData($delivery->event_id);
            $items = $consignment_item;
        }
        return array($send_name, $send_tel, $send_addr, $rcv_name, $rcv_tel, $rcv_addr, $memo, $items);
    }
}

