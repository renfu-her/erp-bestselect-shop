<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Delivery\BackStatus;
use App\Enums\Delivery\Event;
use App\Enums\Delivery\LogisticStatus;
use App\Enums\DlvBack\DlvBackPapaType;
use App\Enums\DlvBack\DlvBackType;
use App\Enums\DlvOutStock\DlvOutStockType;
use App\Enums\Order\OrderStatus;
use App\Enums\Purchase\LogEventFeature;
use App\Enums\Payable\ChequeStatus;
use App\Enums\StockEvent;
use App\Enums\Supplier\Payment;
use App\Exports\Delivery\DeliveryListExport;
use App\Helpers\IttmsDBB;
use App\Http\Controllers\Controller;
use App\Models\AccountPayable;
use App\Models\Consignment;
use App\Models\ConsignmentItem;
use App\Models\CsnOrder;
use App\Models\CsnOrderFlow;
use App\Models\CsnOrderItem;
use App\Models\DayEnd;
use App\Models\Delivery;
use App\Models\Depot;
use App\Models\DlvBack;
use App\Models\DlvBacPapa;
use App\Models\DlvElementBack;
use App\Models\DlvOutStock;
use App\Models\GeneralLedger;
use App\Models\Logistic;
use App\Models\LogisticFlow;
use App\Models\Order;
use App\Models\OrderFlow;
use App\Models\OrderInvoice;
use App\Models\OrderItem;
use App\Models\PayingOrder;
use App\Models\PayableAccount;
use App\Models\PayableCash;
use App\Models\PayableCheque;
use App\Models\PayableRemit;
use App\Models\PayableForeignCurrency;
use App\Models\PayableOther;
use App\Models\PayableDefault;
use App\Models\PcsStatisInbound;
use App\Models\Petition;
use App\Models\ProductStock;
use App\Models\ProductStyle;
use App\Models\PurchaseInbound;
use App\Models\PurchaseLog;
use App\Models\ReceivedDefault;
use App\Models\ReceiveDepot;
use App\Models\ShipmentCategory;
use App\Models\ShipmentGroup;
use App\Models\SubOrders;
use App\Models\Temps;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class DeliveryCtrl extends Controller
{
    private $has_csn = [['false', '否'], ['true', '是']];
    private $has_back_sn = [['all', '不限'], ['1', '只顯示有銷貨退回單']];

    public function index(Request $request)
    {
        $query = $request->query();
        $cond = $this->initIndexQueryParam($query);

        $delivery = Delivery::getList($cond)->paginate($cond['data_per_page'])->appends($query);
        $order_status = [];
        foreach (OrderStatus::asArray() as $item) {
            $order_status[$item] = OrderStatus::getDescription($item);
        }

        $uniqueSubOrderDataList = [];
        $subOrderIdArray = [];
        foreach ($delivery as $deliveryDatum) {
            if (!in_array($deliveryDatum->sub_order_id, $subOrderIdArray)){
                $subOrderIdArray[] = $deliveryDatum->sub_order_id;
                $deliveryDatum->products = DB::table('ord_items')
                    ->where('sub_order_id', $deliveryDatum->sub_order_id)
                    ->select('product_title', 'qty')
                    ->get();
                $uniqueSubOrderDataList[] = $deliveryDatum;
            }
        }
        return view('cms.commodity.delivery.list', [
            'dataList' => $delivery,
            'uniqueSubOrderDataList' => $uniqueSubOrderDataList,
            'depotList' => Depot::all(),
            'shipmentCategory' => ShipmentCategory::all(),
            'logisticStatus' => LogisticStatus::asArray(),
            'order_status' => $order_status,
            'has_csn' => $this->has_csn,
            'searchParam' => $cond,
            'data_per_page' => $cond['data_per_page'],
            'temps' => Temps::get(),
            'has_back_sn' => $this->has_back_sn,
        ]);
    }

    public function exportList(Request $request)
    {
        $query = $request->query();
        $cond = $this->initIndexQueryParam($query);

        $data_list = Delivery::getList($cond)->get();

        $data_arr = [];
        if (null != $data_list) {
            foreach ($data_list as $item) {
                $back_detail = (null != $item->back_detail)? json_decode($item->back_detail): null;

                $back_sn = "";
                $back_status = "";
                if(null != $back_detail && 0 < count($back_detail)) {
                    foreach ($back_detail as $item_key => $item_data) {
                        $endstr = ($item_key != count($back_detail) - 1) ? PHP_EOL: '';
                        $back_sn .= $item_data->sn. $endstr;
                        $back_status .= $item_data->back_status. $endstr;
                    }
                }

                $data_arr[] = [
                    $item->delivery_sn,
                    $item->event_sn,
                    $item->total_price,
                    $item->depot_name ?? '-',
                    $item->order_status,
                    $item->logistic_status,
                    $item->ship_category_name,
                    $item->method ?? '-',
                    $item->temp_name,
                    $item->depot_temp_name,
                    $item->sed_name,
                    $item->rec_name,
                    $item->rec_address,
                    $back_sn,
                    $back_status
                ];
            }
        }
        $column_name = [
            '出貨單號',
            '單據編號',
            '訂單金額',
            '寄件倉',
            '訂單狀態',
            '物流狀態',
            '物流分類1',
            '物流分類2',
            '物流溫層',
            '自取倉溫層',
            '寄件人姓名',
            '收件人姓名',
            '收件人地址',
            '退貨單號',
            '退貨狀態',
        ];

        $export= new DeliveryListExport([
            $column_name,
            $data_arr,
        ]);

        return Excel::download($export, 'delivery_list.xlsx');
    }

    private function initIndexQueryParam($query) {
        $cond = [];
        $cond['delivery_sn'] = Arr::get($query, 'delivery_sn', null);
        $cond['event_sn'] = Arr::get($query, 'event_sn', null);
        $cond['receive_depot_id'] = Arr::get($query, 'receive_depot_id', []);
        $cond['ship_method'] = Arr::get($query, 'ship_method', []);
        $cond['logistic_status_code'] = Arr::get($query, 'logistic_status_code', []);
        $cond['ship_category'] = Arr::get($query, 'ship_category', []);
        $cond['order_status'] = Arr::get($query, 'order_status', []);

        $cond['order_sdate'] = Arr::get($query, 'order_sdate', null);
        $cond['order_edate'] = Arr::get($query, 'order_edate', null);
        $cond['delivery_sdate'] = Arr::get($query, 'delivery_sdate', null);
        $cond['delivery_edate'] = Arr::get($query, 'delivery_edate', null);
        $cond['has_csn'] = Arr::get($query, 'has_csn', $this->has_csn[0][0]);
        $cond['has_back_sn'] = Arr::get($query, 'has_back_sn', $this->has_back_sn[0][0]);
        $cond['back_sn'] = Arr::get($query, 'back_sn', null);
        $cond['ship_temp_id'] = Arr::get($query, 'ship_temp_id', []);
        $cond['depot_temp_id'] = Arr::get($query, 'depot_temp_id', []);

        $cond['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page'));

        return $cond;
    }


    public function create($event, $eventId)
    {
        $rsp_arr = [
            'event' => $event,
            'eventId' => $eventId,
        ];
        $delivery = null;
        $delivery_id = null;
        $event_sn = '';
        if(Event::order()->value == $event) {
            $sub_order = SubOrders::getListWithShiGroupById($eventId)->get()->first();
            $event_sn = $sub_order->sn;
            if (null == $sub_order) {
                return abort(404);
            }
            $rsp_arr['order_id'] = $sub_order->order_id;

            // 出貨單號ID
            $delivery = Delivery::getData($event, $sub_order->id)->get()->first();
            $delivery_id = $delivery->id;
            $ord_items_arr = ReceiveDepot::getOrderShipItemWithDeliveryWithReceiveDepotList($event, $eventId, $delivery_id);
        } else if(Event::consignment()->value == $event) {
            // 出貨單號ID
            $delivery = Delivery::getData($event, $eventId)->get()->first();
            $delivery_id = $delivery->id;
            $ord_items_arr = ReceiveDepot::getCSNShipItemWithDeliveryWithReceiveDepotList($event, $eventId, $delivery_id);
            $consignment = Consignment::where('id', $delivery->event_id)->get()->first();
            $event_sn = $consignment->sn;
            $rsp_arr['depot_id'] = $consignment->send_depot_id;
        } else if(Event::csn_order()->value == $event) {
            // 出貨單號ID
            $delivery = Delivery::getData($event, $eventId)->get()->first();
            $delivery_id = $delivery->id;
            $ord_items_arr = ReceiveDepot::getCSNOrderShipItemWithDeliveryWithReceiveDepotList($event, $eventId, $delivery_id);
            $csn_order = CsnOrder::where('id', $delivery->event_id)->get()->first();
            $event_sn = $csn_order->sn;
            $rsp_arr['depot_id'] = $csn_order->depot_id;
        }

        $rsp_arr['event'] = $event;
        $rsp_arr['delivery'] = $delivery;
        $rsp_arr['delivery_id'] = $delivery_id;
        $rsp_arr['sn'] = $delivery->sn;
        $rsp_arr['ord_items_arr'] = $ord_items_arr;
        $rsp_arr['formAction'] = Route('cms.delivery.store', [
            'deliveryId' => $delivery_id,
        ], true);
        $rsp_arr['breadcrumb_data'] = ['sn' => $event_sn, 'parent' => $event ];

        return view('cms.commodity.delivery.edit', $rsp_arr);
    }

    public function store(Request $request, int $delivery_id)
    {
        $can_diff_depot = $request->input('can_diff_depot') ?? 0; //開放不同倉庫出貨 預設為關
        $errors = [];
        $delivery = Delivery::where('id', '=', $delivery_id)->get()->first();
        if (null != $delivery->audit_date) {
            $errors['error_msg'] = '不可重複送出審核';
        } else {
            $re = ReceiveDepot::setUpShippingData($delivery->event, $delivery->event_id, $delivery_id, $can_diff_depot, $request->user()->id, $request->user()->name);
            if ($re['success'] == '1') {
                wToast('儲存成功');
                return redirect(Route('cms.delivery.create', [
                    'event' => $delivery->event,
                    'eventId' => $delivery->event_id,
                ], true));
            }
            $errors['error_msg'] = $re['error_msg'];
        }

        return redirect()->back()->withInput()->withErrors($errors);
    }

    public function store_cancle(Request $request, int $delivery_id)
    {
        $errors = [];
        $delivery = Delivery::where('id', '=', $delivery_id)->get()->first();
        $rcvDepot = ReceiveDepot::where('delivery_id', '=', $delivery_id)->where('back_qty', '>', 0)->get();
        if (null == $delivery->audit_date) {
            $errors['error_msg'] = '尚未送出審核';
        } else if (null != $rcvDepot && 0 < count($rcvDepot)) {
            $errors['error_msg'] = '已有退貨入庫 不可取消';
        } else {
            $re = ReceiveDepot::cancleShippingData($delivery->event, $delivery->event_id, $delivery_id, $request->user()->id, $request->user()->name);
            if ($re['success'] == '1') {
                wToast('取消出貨成功');
                return redirect(Route('cms.delivery.create', [
                    'event' => $delivery->event,
                    'eventId' => $delivery->event_id,
                ], true));
            }
            $errors['error_msg'] = $re['error_msg'];
        }

        return redirect()->back()->withInput()->withErrors($errors);
    }

    //刪除出貨單
    public function destroy_readyUse(Request $request, $event, int $event_id)
    {
        $re = Delivery::deleteByEventId($event, $event_id);
        if ($re['success'] == '1') {
            wToast('刪除完成');
        } else {
            wToast($re['error_msg']);
        }
        if ($event == Event::order()->value) {
            return redirect(Route('cms.order.detail', [$event_id], true));
        } else if ($event == Event::consignment()->value) {
            return redirect(Route('cms.consignment.edit', [$event_id], true));
        } else if ($event == Event::csn_order()->value) {
            return redirect(Route('cms.consignment.edit', [$event_id], true));
        } else {
            return redirect(Route('cms.order.detail', [$event_id], true));
        }
    }

    //刪除出貨單收貨倉數量
    public function destroyItem(Request $request, $event, $eventId, int $receiveDepotId)
    {
        ReceiveDepot::deleteById($receiveDepotId);
        wToast('刪除成功');

        return redirect(Route('cms.delivery.create', [
            'event' => $event,
            'eventId' => $eventId,], true));
    }

    //缺貨
    public function out_stock(Request $request, $event, $eventId)
    {
        $delivery = Delivery::getData($event, $eventId)->get()->first();
        if (null == $delivery) {
            return abort(404);
        }
        $dlv_os_other = DlvOutStock::getOtherDataWithDeliveryID($delivery->id)->get();
        //判斷有資料代表已做過 直接返回明細列表
        if (isset($dlv_os_other) && 0 < count($dlv_os_other)) {
            return redirect(Route('cms.delivery.out_stock_detail', [
                'event' => $delivery->event,
                'eventId' => $delivery->event_id,
            ], true));
        }
        return $this->do_out_stock_edit($request, $event, $eventId, 'create');
    }

    public function out_stock_delete(Request $request, $delivery_id)
    {
        $delivery = Delivery::where('id', '=', $delivery_id)->first();
        $items = Delivery::delivery_item($delivery->id, 'out', null)->first();
        if (Event::order()->value != $delivery->event) {
            wToast('此功能只提供訂單使用');
            return redirect()->back();
        }
        if (null != $delivery->audit_date) {
            wToast('已出貨 無法刪除');
            return redirect()->back();
        } else if(null != $items && null != $items->po_sn) {
            wToast('已有付款單 無法刪除');
            return redirect()->back();
        } else {
            $msg = IttmsDBB::transaction(function () use ($request, $delivery, $delivery_id) {
                $out_prd = DlvOutStock::where('delivery_id', $delivery_id)->where('type', '=', DlvOutStockType::product()->value)->get();

                $sub_order = SubOrders::where('id', '=', $delivery->event_id)->first();
                $order = Order::where('id', '=', $sub_order->order_id)->first();

                if (null != $out_prd && 0 < count($out_prd)) {
                    foreach ($out_prd as $prd_item) {
                        if (OrderStatus::Canceled()->value != $order->status_code) {
                            //有訂單取消則不做 否則計算
                            if (false == is_null($prd_item->qty) && 0 != $prd_item->qty) {
                                ProductStock::stockChange($prd_item->product_style_id,
                                    $prd_item->qty * -1, StockEvent::out_stock_cancle()->value, $delivery->event_id, $delivery->event_sn. ' '. $prd_item->sku. ' ' . "缺貨");

                                ProductStyle::willBeShipped($prd_item->product_style_id, $prd_item->qty);
                            }
                        }
                    }
                }

                Delivery::where('id', $delivery_id)->update([
                    'out_date' => null
                    , 'out_sn' => null
                    , 'out_memo' => null
                    , 'out_user_id' => null
                    , 'out_user_name' => null
                ]);
                DlvOutStock::where('delivery_id', $delivery_id)->delete();

                return ['success' => 1];
            });

            if ($msg['success'] == 0) {
                wToast($msg['error_msg']);
            } else {
                wToast('刪除成功');
            }
            return redirect()->back();
        }
    }

    public function out_stock_store(Request $request, int $delivery_id)
    {
        $request->validate([
            'method' => 'nullable|string',
            'dlv_memo' => 'nullable|string',
            'id.*' => 'nullable|numeric',
            'event_item_id.*' => 'required|numeric',
            'product_style_id.*' => 'required|string',
            'product_title.*' => 'required|string',
            'sku.*' => 'required|string',
            'price.*' => 'required|numeric',
            'bonus.*' => 'required|numeric',
            'dividend.*' => 'required|numeric',
            'origin_qty.*' => 'required|numeric',
            'back_qty.*' => 'required|numeric',
            'memo.*' => 'nullable|string',
            'show.*' => 'filled|bool',

            'back_item_id.*' => 'nullable|numeric',
            'bgrade_id.*' => 'required_with:btype|numeric',
            'btitle.*' => 'required|string',
            'bprice.*' => 'required|numeric',
            'bqty.*' => 'required|numeric',
            'bmemo.*' => 'nullable|string',
        ]);

        $errors = [];
        $delivery = Delivery::where('id', $delivery_id)->first();
        if (Event::order()->value != $delivery->event) {
            dd('此功能只提供訂單使用');
        }

        $msg = IttmsDBB::transaction(function () use ($request, $delivery, $delivery_id) {
            $method = $request->input('method', null);
            $dlv_memo = $request->input('dlv_memo', null);
            $out_sn = str_replace("DL","OUT",$delivery->sn); //將出貨單號改為銷貨退回單號
            Delivery::where('id', $delivery_id)->update([
                'out_date' => date("Y-m-d H:i:s")
                , 'out_sn' => $out_sn //寫入缺貨單號
                , 'out_memo' => $dlv_memo
                , 'out_user_id' => $request->user()->id
                , 'out_user_name' => $request->user()->name
            ]);

            $sub_order = SubOrders::where('id', '=', $delivery->event_id)->first();
            $order = Order::where('id', '=', $sub_order->order_id)->first();

            $input_items = $request->only('id', 'event_item_id', 'product_style_id', 'product_title', 'sku', 'price', 'bonus', 'dividend', 'origin_qty', 'back_qty', 'memo', 'show');

            if (isset($input_items['id']) && 0 < count($input_items['id'])) {
                if(true == isset($input_items['id'][0])) {
                    DB::rollBack();
                    return ['success' => 0, 'error_msg' => '不提供缺貨編輯功能'];
                    //已有資料 做編輯
                    for($i = 0; $i < count($input_items['id']); $i++) {
                        DlvOutStock::where('id', '=', $input_items['id'][$i])->update([
                            'product_title' => $input_items['product_title'][$i],
                            'price' => $input_items['price'][$i],
                            'bonus' => $input_items['bonus'][$i],
                            'dividend' => $input_items['dividend'][$i],
                            'qty' => $input_items['back_qty'][$i],
                            'memo' => $input_items['memo'][$i],
                            'show' => $input_items['show'][$i] ?? false,
                        ]);
                    }
                } else {
                    $data = [];
                    $default_grade_id = ReceivedDefault::where('name', '=', 'product')->first()->default_grade_id;
                    $curr_date = date('Y-m-d H:i:s');
                    for($i = 0; $i < count($input_items['id']); $i++) {
                        $addItem = [
                            'delivery_id' => $delivery_id,
                            'event_item_id' => $input_items['event_item_id'][$i],
                            'product_style_id' => $input_items['product_style_id'][$i],
                            'sku' => $input_items['sku'][$i],
                            'product_title' => $input_items['product_title'][$i],
                            'price' => $input_items['price'][$i],
                            'bonus' => $input_items['bonus'][$i],
                            'dividend' => $input_items['dividend'][$i],
                            'origin_qty' => $input_items['origin_qty'][$i],
                            'qty' => $input_items['back_qty'][$i],
                            'memo' => $input_items['memo'][$i],
                            'show' => $input_items['show'][$i] ?? false,
                            'type' => DlvBackType::product()->value,
                            'grade_id' => $default_grade_id,
                            'created_at' => $curr_date,
                            'updated_at' => $curr_date,
                        ];
                        $data[] = $addItem;

                        if (OrderStatus::Canceled()->value != $order->status_code) {
                            //有訂單取消則不做 否則計算
                            if (false == is_null($input_items['back_qty'][$i]) && 0 != $input_items['back_qty'][$i]) {
                                ProductStock::stockChange($input_items['product_style_id'][$i],
                                    $input_items['back_qty'][$i], StockEvent::out_stock()->value, $delivery->event_id, $delivery->event_sn. ' '. $input_items['sku'][$i]. ' '. "缺貨");

                                ProductStyle::willBeShipped($input_items['product_style_id'][$i], $input_items['back_qty'][$i] * -1);
                            }
                        }
                    }
                    DlvOutStock::insert($data);
                }
            }
            $input_other_items = $request->only('back_item_id', 'bgrade_id', 'btitle', 'bprice', 'bqty', 'bmemo');

            $dArray = array_diff(DlvOutStock::where('delivery_id', $delivery_id)->where('type', '<>', DlvOutStockType::product()->value)->pluck('id')->toArray()
                , array_intersect_key($input_other_items['back_item_id']?? [], $input_other_items['bgrade_id']?? [] )
            );
            if($dArray) DlvOutStock::destroy($dArray);

            if (isset($input_other_items['bgrade_id']) && 0 < count($input_other_items['bgrade_id'])) {
                foreach(request('back_item_id') as $key => $value){
                    if(true == isset($input_other_items['bgrade_id'][$key])) {
                        if(true == isset($input_other_items['back_item_id'][$key])) {
                            DlvOutStock::where('id', '=', $input_other_items['back_item_id'][$key])->update([
                                'grade_id' => $input_other_items['bgrade_id'][$key],
                                'product_title' => $input_other_items['btitle'][$key],
                                'price' => $input_other_items['bprice'][$key],
                                'qty' => $input_other_items['bqty'][$key],
                                'memo' => $input_other_items['bmemo'][$key],
                            ]);
                        } else {
                            if (false == isset($input_other_items['bgrade_id'][$key])) {
                                DB::rollBack();
                                return ['success' => 0, 'error_msg' => '未填入會計科目'];
                            }
                            DlvOutStock::create([
                                'delivery_id' => $delivery_id,
                                'grade_id' => $input_other_items['bgrade_id'][$key],
                                'type' => DlvOutStockType::other()->value,
                                'product_title' => $input_other_items['btitle'][$key],
                                'price' => $input_other_items['bprice'][$key],
                                'qty' => $input_other_items['bqty'][$key],
                                'memo' => $input_other_items['bmemo'][$key],
                                'sku' => '',
                                'origin_qty' => 0,
                                'bonus' => '',
                                'dividend' => 0,
                                'show' => 1,
                            ]);
                        }
                    }
                }
            }

            return ['success' => 1];
        });
        if ($msg['success'] == 0) {
            throw ValidationException::withMessages(['item_error' => $msg['error_msg']]);
        } else {
            wToast('儲存成功');
            return redirect(Route('cms.delivery.out_stock_detail', [
                'event' => $delivery->event,
                'eventId' => $delivery->event_id,
            ], true));
        }
    }

    public function out_stock_edit(Request $request, $event, $eventId)
    {
        dd('無此功能 因會需要計算最新可售數量');
        return $this->do_out_stock_edit($request, $event, $eventId, 'edit');
    }

    private function do_out_stock_edit(Request $request, $event, $eventId, $method) {
        $delivery = Delivery::getData($event, $eventId)->get()->first();
        if (null == $delivery) {
            return abort(404);
        }
        //其他項目
        $rsp_arr['dlv_other_items'] = [];
        if ('create' == $method) {
            $product_title = null;
            $price = null;
            if(Event::order()->value == $event) {
                $sub_order = SubOrders::where('id', $delivery->event_id)->get()->first();
                $product_title = $sub_order->ship_event;
                $price = $sub_order->dlv_fee;
            } else if(Event::consignment()->value == $event) {
                $logistic = DB::table(app(Logistic::class)->getTable() . ' as lgt')
                    ->leftJoin(app(ShipmentGroup::class)->getTable(). ' as sgroup', 'sgroup.id', '=', 'lgt.ship_group_id')
                    ->whereNull('lgt.deleted_at')
                    ->where('lgt.delivery_id', '=', $delivery->id)
                    ->select('lgt.cost', 'sgroup.name')
                    ->first();
                if (null != $logistic) {
                    $product_title = $logistic->name ?? '';
                    $price = $logistic->cost;
                }
            }
            $qty = $product_title ? 1: null;
            $rsp_arr['dlv_other_items'] = json_decode(json_encode([[
                'id' => null,
                'delivery_id' => $delivery->id,
                'type' => DlvOutStockType::other()->value,
                'product_title' => $product_title,
                'price' => $price,
                'qty' => $qty,
            ]]));
        } else {
            $dlv_os_other = DlvOutStock::getOtherDataWithDeliveryID($delivery->id)->get();
            if (isset($dlv_os_other) && 0 < count($dlv_os_other)) {
                $rsp_arr['dlv_other_items'] = $dlv_os_other;
            }
        }
        if(Event::order()->value == $event) {
            $sub_order = SubOrders::where('id', $delivery->event_id)->get()->first();
            $rsp_arr['order_id'] = $sub_order->order_id;
        }

        //缺貨商品款式
        $ord_items = null;
        $dlv_os = DlvOutStock::getDataWithDeliveryID($delivery->id)->get();
        if (isset($dlv_os) && 0 < count($dlv_os)) {
            $ord_items = $dlv_os;
        } else if ('create' == $method){
            if (Event::order()->value == $event) {
                $ord_items = DB::table(app(OrderItem::class)->getTable(). ' as ord_item')
                    ->where('ord_item.order_id', '=', $sub_order->order_id)
                    ->where('ord_item.sub_order_id', '=', $eventId)
                    ->select('ord_item.id as event_item_id'
                        , 'ord_item.product_style_id'
                        , 'ord_item.product_title'
                        , 'ord_item.sku'
                        , 'ord_item.price'
                        , 'ord_item.bonus'
                        , 'ord_item.qty as origin_qty'
                    )->get();
            } else if (Event::consignment()->value == $event) {
                $ord_items = DB::table(app(ConsignmentItem::class)->getTable(). ' as ord_item')
                    ->where('ord_item.consignment_id', '=', $eventId)
                    ->whereNull('ord_item.deleted_at')
                    ->select('ord_item.id as event_item_id'
                        , 'ord_item.product_style_id'
                        , 'ord_item.title as product_title'
                        , 'ord_item.sku'
                        , 'ord_item.price'
                        , DB::raw('@0:="0" as bonus')
                        , 'ord_item.num as origin_qty'
                    )->get();
            } else if (Event::csn_order()->value == $event) {
                $ord_items = DB::table(app(CsnOrderItem::class)->getTable(). ' as ord_item')
                    ->where('ord_item.csnord_id', '=', $eventId)
                    ->whereNull('ord_item.deleted_at')
                    ->select('ord_item.id as event_item_id'
                        , 'ord_item.product_style_id'
                        , 'ord_item.title as product_title'
                        , 'ord_item.sku'
                        , 'ord_item.price'
                        , DB::raw('@0:="0" as bonus')
                        , 'ord_item.num as origin_qty'
                    )->get();
            }
        }

        $total_grades = GeneralLedger::total_grade_list();

        $rsp_arr['method'] = $method;
        $rsp_arr['delivery'] = $delivery;
        $rsp_arr['event'] = $event;
        $rsp_arr['eventId'] = $eventId;
        $rsp_arr['ord_items'] = $ord_items;
        $rsp_arr['total_grades'] = $total_grades;
        $rsp_arr['formAction'] = Route('cms.delivery.out_stock_store', [
            'deliveryId' => $delivery->id,
        ], true);
        $rsp_arr['breadcrumb_data'] = ['sn' => $delivery->sn, 'parent' => $event ];
        return view('cms.commodity.delivery.out_stock', $rsp_arr);
    }

    //銷貨退回明細
    public function out_stock_detail($event, $eventId)
    {
        $rsp_arr = $this->getOutStockDetailRsp($event, $eventId);
        return view('cms.commodity.delivery.out_stock_detail', $rsp_arr);
    }

    public function print_out_stock(Request $request, $event, $eventId)
    {
        $rsp_arr = $this->getOutStockDetailRsp($event, $eventId);
        $rsp_arr['type_display'] = 'back';
        $rsp_arr['user'] = $request->user();
        return view('doc.print_out_stock', $rsp_arr);
    }

    private function getOutStockDetailRsp($event, $eventId) {
        $delivery = Delivery::getData($event, $eventId)->get()->first();
        if (null == $delivery) {
            return abort(404);
        }
        $item_table = null;
        $dlvBack = null;
        $order = null;
        $orderInvoice = null;
        $logistic = null;
        $rsp_arr['has_payable_data_back'] = false; //退貨付款單已有付款紀錄
        $source_type = null;
        if (Event::order()->value == $delivery->event) {
            $subOrder = SubOrders::where('id', '=', $delivery->event_id)->first();
            $order = Order::orderDetail($subOrder->order_id)->get()->first();
            $rsp_arr['subOrders'] = $subOrder;
            $rsp_arr['order'] = $order;
            $item_table = app(OrderItem::class)->getTable();
            $source_type = app(Order::class)->getTable();
        } else if (Event::consignment()->value == $delivery->event) {
            $order = DB::table(app(Consignment::class)->getTable(). ' as csn')
                ->leftJoin(app(Depot::class)->getTable(). ' as depot', 'depot.id', '=', 'csn.receive_depot_id')
                ->where('csn.id', $delivery->event_id)
                ->whereNull('csn.deleted_at')
                ->select('csn.sn as sn'
                    , 'csn.send_depot_name as sed_name'
                    , 'depot.name as ord_name', 'depot.tel as ord_phone', 'depot.addr as ord_address'
                    , 'depot.name as rec_name', 'depot.tel as rec_phone', 'depot.addr as rec_address'
                )
                ->first();
            $rsp_arr['order'] = $order;
            $item_table = app(ConsignmentItem::class)->getTable();
            $source_type = app(Consignment::class)->getTable();
        } else if (Event::csn_order()->value == $delivery->event) {
            $order = DB::table(app(CsnOrder::class)->getTable(). ' as csn')
                ->leftJoin(app(Depot::class)->getTable(). ' as depot', 'depot.id', '=', 'csn.depot_id')
                ->where('csn.id', $delivery->event_id)
                ->whereNull('csn.deleted_at')
                ->select('csn.sn as sn', 'depot.name as ord_name', 'depot.tel as ord_phone', 'depot.addr as ord_address')
                ->first();
            $rsp_arr['order'] = $order;
            $item_table = app(CsnOrderItem::class)->getTable();
            $source_type = app(CsnOrder::class)->getTable();
        }
        if (null != $source_type) {
            $orderInvoice = OrderInvoice::where('source_type', '=', $source_type)
                ->where('source_id', '=', $delivery->event_id)->first();
            $rsp_arr['orderInvoice'] = $orderInvoice;
        }
        //判斷該付款單是否有付款紀錄
        $paying_order = PayingOrder::where([
            'source_type' => app(Delivery::class)->getTable(),
            'source_id' => $delivery->id,
            'source_sub_id' => null,
            'type' => 8,
            'deleted_at' => null,
            ])
            ->first();
        if (isset($paying_order)) {
            $payable_data = PayingOrder::get_payable_detail($paying_order->id);
            if (0 < count($payable_data)) {
                $rsp_arr['has_payable_data_back'] = true;
            }
        }

        if (isset($item_table)) {
            $dlvBack = DlvOutStock::getDataWithDeliveryID($delivery->id)->get();
        }

        $dlv_other_items = DlvOutStock::getOtherDataWithDeliveryID($delivery->id)->get();

//        $logistic = Logistic::where('id', '=', $delivery->event_id)->first();

        $logistic = DB::table(app(Logistic::class)->getTable(). ' as lgt_tb')
            ->leftJoin(app(ShipmentGroup::class)->getTable(). ' as shi_group', 'shi_group.id', '=', 'lgt_tb.ship_group_id')
            ->select(
                'lgt_tb.*', 'shi_group.name as group_name'
            )
            ->where('lgt_tb.delivery_id', '=', $delivery->id)
            ->first();

        $rsp_arr['logistic'] = $logistic;

        $rsp_arr['event'] = $event;
        $rsp_arr['delivery'] = $delivery;
        $rsp_arr['delivery_id'] = $delivery->id;
        $rsp_arr['sn'] = $delivery->sn;
        $rsp_arr['dlvBack'] = $dlvBack;
        $rsp_arr['dlv_other_items'] = $dlv_other_items;
        $rsp_arr['breadcrumb_data'] = ['sn' => $delivery->event_sn, 'parent' => $event ];
        $items = Delivery::delivery_item($delivery->id, 'out', null)->get();
        foreach ($items as $key => $value) {
            $items[$key]->delivery_items = json_decode($value->delivery_items);
        }
        $rsp_arr['items'] = $items->first();
        $rsp_arr['po_check'] = $source_type == app(Order::class)->getTable() ? PayingOrder::source_confirmation($source_type, $order->id, null, 9) : true;
        return $rsp_arr;
    }

    public function back_list(Request $request, int $delivery_id = null)
    {
        $delivery = Delivery::where('id', '=', $delivery_id)->first();
        $bacPapa = DlvBacPapa::getDataWithDelivery($delivery_id)->get();

        $rsp_arr = [];
        if(null != $delivery && Event::order()->value == $delivery->event) {
            $sub_order = SubOrders::where('id', $delivery->event_id)->get()->first();
            $rsp_arr['order_id'] = $sub_order->order_id;
        }

        $rsp_arr['delivery'] = $delivery?? null;
        $rsp_arr['event'] = $delivery->event?? null;
        $rsp_arr['eventId'] = $delivery->event_id?? null;
        $rsp_arr['dataList'] = $bacPapa;

        $rsp_arr['breadcrumb_data'] = ['sn' => $delivery->sn?? null, 'parent' => $delivery->event?? null ];

        return view('cms.commodity.delivery.back_list', $rsp_arr);
    }

    public function back_create(Request $request, int $delivery_id)
    {
        $delivery = Delivery::where('id', '=', $delivery_id)->first();
        if (null == $delivery) {
            return abort(404);
        }
        $rsp_arr['dlv_other_items'] = [];

        $product_title = null;
        $price = null;
        if(Event::order()->value == $delivery->event) {
            $sub_order = SubOrders::where('id', $delivery->event_id)->get()->first();
            $product_title = $sub_order->ship_event;
            $price = $sub_order->dlv_fee;
        } else if(Event::consignment()->value == $delivery->event) {
            $logistic = DB::table(app(Logistic::class)->getTable() . ' as lgt')
                ->leftJoin(app(ShipmentGroup::class)->getTable(). ' as sgroup', 'sgroup.id', '=', 'lgt.ship_group_id')
                ->whereNull('lgt.deleted_at')
                ->where('lgt.delivery_id', '=', $delivery->id)
                ->select('lgt.cost', 'sgroup.name')
                ->first();
            if (null != $logistic) {
                $product_title = $logistic->name ?? '';
                $price = $logistic->cost;
            }
        }
        $qty = $product_title ? 1: null;
        $rsp_arr['dlv_other_items'] = json_decode(json_encode([[
            'id' => null,
            'delivery_id' => $delivery->id,
            'type' => DlvBackType::other()->value,
            'product_title' => $product_title,
            'price' => $price,
            'qty' => $qty,
        ]]));

        if(Event::order()->value == $delivery->event) {
            $sub_order = SubOrders::where('id', $delivery->event_id)->get()->first();
            $rsp_arr['order_id'] = $sub_order->order_id;
        }

        //退貨商品款式
        $ord_items = [];

        if(Event::order()->value == $delivery->event) {
            $ord_items = DB::table(app(OrderItem::class)->getTable() . ' as ord_item')
                ->leftJoin(app(DlvOutStock::class)->getTable(). ' as outs', function ($join) use($delivery) {
                    $join->on('outs.event_item_id', '=', 'ord_item.id');
                    $join->where('outs.delivery_id', '=', $delivery->id);
                })
                ->where('ord_item.sub_order_id', '=', $delivery->event_id)
                ->select('ord_item.id as event_item_id'
                    , 'ord_item.product_style_id'
                    , 'ord_item.product_title'
                    , 'ord_item.sku'
                    , 'ord_item.price'
                    , 'ord_item.bonus'
                    , DB::raw('(ord_item.qty - ifnull(outs.qty, 0)) as origin_qty')
                )->get();
        } elseif(Event::consignment()->value == $delivery->event) {
            $ord_items = DB::table(app(ConsignmentItem::class)->getTable() . ' as ord_item')
                ->leftJoin(app(DlvOutStock::class)->getTable(). ' as outs', function ($join) use($delivery) {
                    $join->on('outs.event_item_id', '=', 'ord_item.id');
                    $join->where('outs.delivery_id', '=', $delivery->id);
                })
                ->where('ord_item.consignment_id', '=', $delivery->event_id)
                ->whereNull('ord_item.deleted_at')
                ->select('ord_item.id as event_item_id'
                    , 'ord_item.product_style_id'
                    , 'ord_item.title as product_title'
                    , 'ord_item.sku'
                    , 'ord_item.price'
                    , DB::raw('@0:="0" as bonus')
                    , DB::raw('(ord_item.num - ifnull(outs.qty, 0)) as origin_qty')
                )->get();
        } elseif(Event::csn_order()->value == $delivery->event) {
            $ord_items = DB::table(app(CsnOrderItem::class)->getTable() . ' as ord_item')
                ->leftJoin(app(DlvOutStock::class)->getTable(). ' as outs', function ($join) use($delivery) {
                    $join->on('outs.event_item_id', '=', 'ord_item.id');
                    $join->where('outs.delivery_id', '=', $delivery->id);
                })
                ->where('ord_item.csnord_id', '=', $delivery->event_id)
                ->whereNull('ord_item.deleted_at')
                ->select('ord_item.id as event_item_id'
                    , 'ord_item.product_style_id'
                    , 'ord_item.title as product_title'
                    , 'ord_item.sku'
                    , 'ord_item.price'
                    , DB::raw('@0:="0" as bonus')
                    , DB::raw('(ord_item.num - ifnull(outs.qty, 0)) as origin_qty')
                )->get();
        }

        $total_grades = GeneralLedger::total_grade_list();

        $rsp_arr['method'] = 'create';
        $rsp_arr['delivery'] = $delivery;
        $rsp_arr['event'] = $delivery->event;
        $rsp_arr['eventId'] = $delivery->event_id;
        $rsp_arr['ord_items'] = $ord_items;
        $rsp_arr['total_grades'] = $total_grades;
        $rsp_arr['formAction'] = Route('cms.delivery.back_create', [
            'deliveryId' => $delivery->id,
        ], true);
        $rsp_arr['breadcrumb_data'] = ['sn' => $delivery->sn, 'parent' => $delivery->event ];

        return view('cms.commodity.delivery.back', $rsp_arr);
    }

    public function back_create_store(Request $request, int $delivery_id) {
        $request->validate([
            'method' => 'nullable|string',
            'dlv_memo' => 'nullable|string',
            'id.*' => 'nullable|numeric',
            'event_item_id.*' => 'required|numeric',
            'product_style_id.*' => 'required|string',
            'product_title.*' => 'required|string',
            'sku.*' => 'required|string',
            'price.*' => 'required|numeric',
            'bonus.*' => 'required|numeric',
            'dividend.*' => 'required|numeric',
            'origin_qty.*' => 'required|numeric',
            'back_qty.*' => 'required|numeric',
            'memo.*' => 'nullable|string',
            'show.*' => 'filled|bool',

            'back_item_id.*' => 'nullable|numeric',
            'bgrade_id.*' => 'required_with:btype|numeric',
            'btitle.*' => 'required|string',
            'bprice.*' => 'required|numeric',
            'bqty.*' => 'required|numeric',
            'bmemo.*' => 'nullable|string',
        ]);

        $errors = [];
        $delivery = Delivery::where('id', '=', $delivery_id)->first();
        $msg = IttmsDBB::transaction(function () use ($request, $delivery) {
            $dlv_memo = $request->input('dlv_memo', null);

            $reBacPapa = DlvBacPapa::createData(DlvBackPapaType::back(), $delivery->id, $dlv_memo);
            if ($reBacPapa['success'] == 0) {
                DB::rollBack();
                return $reBacPapa;
            }
            $bac_papa_id = $reBacPapa['id'];
            //修改狀態改為只在新增時做
            Delivery::changeBackStatus($delivery->id, BackStatus::add_back());
            DlvBacPapa::changeBackStatus($bac_papa_id, BackStatus::add_back());
            if (Event::order()->value == $delivery->event) {
                $subOrder = SubOrders::where('id', '=', $delivery->event_id)->first();
                OrderFlow::changeOrderStatus($subOrder->order_id, OrderStatus::BackProcessing());
            } else if (Event::consignment()->value == $delivery->event) {
            } else if (Event::csn_order()->value == $delivery->event) {
                DB::rollBack();
                return ['success' => 0, 'error_msg' => '寄倉訂購暫無退貨功能'];
                CsnOrderFlow::changeOrderStatus($delivery->event_id, OrderStatus::BackProcessing());
            }

            $reLFCDS = LogisticFlow::createDeliveryStatus($request->user(), $delivery->id, [LogisticStatus::C2000()]);
            if ($reLFCDS['success'] == 0) {
                DB::rollBack();
                return $reLFCDS;
            }

            $reDBS = $this->do_back_store($request, $delivery, $bac_papa_id);
            if ($reDBS['success'] == 0) {
                DB::rollBack();
                return $reDBS;
            }

            return ['success' => 1, 'bac_papa_id' => $bac_papa_id];
        });
        if ($msg['success'] == 0) {
            throw ValidationException::withMessages(['item_error' => $msg['error_msg']]);
        } else {
            wToast('儲存成功');
            return redirect(Route('cms.delivery.back_detail', [
                'bac_papa_id' => $msg['bac_papa_id'],
            ], true));
        }
    }

    //刪除退貨
    public function back_delete(Request $request, int $bac_papa_id) {
        $bacPapa = DlvBacPapa::where('id', '=', $bac_papa_id)->first();
        $delivery = Delivery::where('id', '=', $bacPapa->delivery_id)->first();
        $items = Delivery::delivery_item($delivery->id, 'return', $bac_papa_id)->first();
        if(null != $items && null != $items->po_sn) {
            wToast('已有付款單 無法刪除', ['type' => 'danger']);
            return redirect()->back();
        }

        $elementBackItems = DlvElementBack::where('delivery_id', '=', $bacPapa->delivery_id)
            ->where('bac_papa_id', '=', $bac_papa_id)
            ->get();
        if(null != $elementBackItems && 0 < count($elementBackItems)) {
            wToast('已有退貨入庫 無法刪除', ['type' => 'danger']);
            return redirect()->back();
        }

        DlvBack::where('bac_papa_id', $bac_papa_id)->delete();
        DlvBacPapa::where('id', $bac_papa_id)->delete();
        Delivery::changeBackStatus($delivery->id, BackStatus::del_back());
        DlvBacPapa::changeBackStatus($bac_papa_id, BackStatus::del_back());

        if (Event::order()->value == $delivery->event) {
            $subOrder = SubOrders::where('id', '=', $delivery->event_id)->first();
            OrderFlow::changeOrderStatus($subOrder->order_id, OrderStatus::CancleBack());
        }

        wToast('刪除成功');
        return redirect()->back();
    }

    public function back_store(Request $request, int $bac_papa_id) {
        $request->validate([
            'method' => 'nullable|string',
            'dlv_memo' => 'nullable|string',
            'id.*' => 'nullable|numeric',
            'event_item_id.*' => 'required|numeric',
            'product_style_id.*' => 'required|string',
            'product_title.*' => 'required|string',
            'sku.*' => 'required|string',
            'price.*' => 'required|numeric',
            'bonus.*' => 'required|numeric',
            'dividend.*' => 'required|numeric',
            'origin_qty.*' => 'required|numeric',
            'back_qty.*' => 'required|numeric',
            'memo.*' => 'nullable|string',
            'show.*' => 'filled|bool',

            'back_item_id.*' => 'nullable|numeric',
            'bgrade_id.*' => 'required_with:btype|numeric',
            'btitle.*' => 'required|string',
            'bprice.*' => 'required|numeric',
            'bqty.*' => 'required|numeric',
            'bmemo.*' => 'nullable|string',
        ]);

        $bacPapa = DlvBacPapa::where('id', '=', $bac_papa_id)->first();
        $delivery = Delivery::where('id', '=', $bacPapa->delivery_id)->first();
        $msg = IttmsDBB::transaction(function () use ($request, $delivery, $bac_papa_id) {
            $dlv_memo = $request->input('dlv_memo', null);

            DlvBacPapa::where('id', '=', $bac_papa_id)->update(['memo' => $dlv_memo]);
            $reDBS = $this->do_back_store($request, $delivery, $bac_papa_id);
            if ($reDBS['success'] == 0) {
                DB::rollBack();
                return $reDBS;
            }

            return ['success' => 1];
        });
        if ($msg['success'] == 0) {
            throw ValidationException::withMessages(['item_error' => $msg['error_msg']]);
        } else {
            wToast('儲存成功');
            return redirect(Route('cms.delivery.back_detail', [
                'bac_papa_id' => $bac_papa_id,
            ], true));
        }
    }

    private function do_back_store(Request $request, $delivery, $bac_papa_id) {
        $msg = IttmsDBB::transaction(function () use ($request, $delivery, $bac_papa_id) {
            $input_items = $request->only('id', 'event_item_id', 'product_style_id', 'product_title', 'sku', 'price', 'bonus', 'dividend', 'origin_qty', 'back_qty', 'memo', 'show');
            if (isset($input_items['id']) && 0 < count($input_items['id'])) {
                if(true == isset($input_items['id'][0])) {
                    //已有資料 做編輯
                    for($i = 0; $i < count($input_items['id']); $i++) {
                        DlvBack::where('id', '=', $input_items['id'][$i])->update([
                            'product_title' => $input_items['product_title'][$i],
                            'price' => $input_items['price'][$i],
                            'bonus' => $input_items['bonus'][$i],
                            'dividend' => $input_items['dividend'][$i],
                            'qty' => $input_items['back_qty'][$i],
                            'memo' => $input_items['memo'][$i],
                            'show' => $input_items['show'][$i] ?? false,
                        ]);
                    }
                } else {
                    $data = [];
                    $default_grade_id = ReceivedDefault::where('name', '=', 'product')->first()->default_grade_id;
                    $curr_date = date('Y-m-d H:i:s');
                    for($i = 0; $i < count($input_items['id']); $i++) {
//                        if (0 == $input_items['back_qty'][$i]) {
//                            //判斷數量零的就跳過
//                            //20221228 不跳過 就算數字為零 可能還是會編輯資料
//                            continue;
//                        }
                        $addItem = [
                            'bac_papa_id' => $bac_papa_id,
                            'delivery_id' => $delivery->id,
                            'event_item_id' => $input_items['event_item_id'][$i],
                            'product_style_id' => $input_items['product_style_id'][$i],
                            'sku' => $input_items['sku'][$i],
                            'product_title' => $input_items['product_title'][$i],
                            'price' => $input_items['price'][$i],
                            'bonus' => $input_items['bonus'][$i],
                            'dividend' => $input_items['dividend'][$i],
                            'origin_qty' => $input_items['origin_qty'][$i],
                            'qty' => $input_items['back_qty'][$i],
                            'memo' => $input_items['memo'][$i],
                            'show' => $input_items['show'][$i] ?? false,
                            'type' => DlvBackType::product()->value,
                            'grade_id' => $default_grade_id,
                            'created_at' => $curr_date,
                            'updated_at' => $curr_date,
                        ];
                        //判斷為訂單 則寫入目前訂單款式的bonus
//                        if (Event::order()->value == $delivery->event) {
//                            $orderItem = DB::table(app(OrderItem::class)->getTable() . ' as order_item')
//                                ->where('order_item.id', '=', $input_items['event_item_id'][$i])
//                                ->select('order_item.id', 'order_item.bonus')
//                                ->first();
//                            if (isset($orderItem)) {
//                                $addItem['bonus'] = $orderItem->bonus;
//                            }
//                        }
                        $data[] = $addItem;
                    }
                    DlvBack::insert($data);
                }
            }
            $input_other_items = $request->only('back_item_id', 'bgrade_id', 'btitle', 'bprice', 'bqty', 'bmemo');

            $dArray = array_diff(DlvBack::where('delivery_id', $delivery->id)->where('type', '<>', DlvBackType::product()->value)->pluck('id')->toArray()
                , array_intersect_key($input_other_items['back_item_id']?? [], $input_other_items['bgrade_id']?? [] )
            );
            if($dArray) DlvBack::destroy($dArray);

            if (isset($input_other_items['bgrade_id']) && 0 < count($input_other_items['bgrade_id'])) {
                foreach(request('back_item_id') as $key => $value){
                    if(true == isset($input_other_items['bgrade_id'][$key])) {
                        if(true == isset($input_other_items['back_item_id'][$key])) {
                            DlvBack::where('id', '=', $input_other_items['back_item_id'][$key])->update([
                                'grade_id' => $input_other_items['bgrade_id'][$key],
                                'product_title' => $input_other_items['btitle'][$key],
                                'price' => $input_other_items['bprice'][$key],
                                'qty' => $input_other_items['bqty'][$key],
                                'memo' => $input_other_items['bmemo'][$key],
                            ]);
                        } else {
                            if (false == isset($input_other_items['bgrade_id'][$key])) {
                                DB::rollBack();
                                return ['success' => 0, 'error_msg' => '未填入會計科目'];
                            }
                            DlvBack::create([
                                'bac_papa_id' => $bac_papa_id,
                                'delivery_id' => $delivery->id,
                                'grade_id' => $input_other_items['bgrade_id'][$key],
                                'type' => DlvBackType::other()->value,
                                'product_title' => $input_other_items['btitle'][$key],
                                'price' => $input_other_items['bprice'][$key],
                                'qty' => $input_other_items['bqty'][$key],
                                'memo' => $input_other_items['bmemo'][$key],
                                'sku' => '',
                                'origin_qty' => 0,
                                'bonus' => '',
                                'dividend' => 0,
                                'show' => 1,
                            ]);
                        }
                    }
                }
            }
            return ['success' => 1];
        });
        return $msg;
    }

    public function back_edit(Request $request, $bac_papa_id)
    {
        $bacPapa = DlvBacPapa::where('id', '=', $bac_papa_id)->first();
        $delivery = Delivery::where('id', '=', $bacPapa->delivery_id)->first();
        if (null == $delivery) {
            return abort(404);
        }
        $elementBacks = DlvElementBack::where('bac_papa_id', '=', $bac_papa_id)->get();
        $rsp_arr['elebacks'] = [];
        //其他項目
        $rsp_arr['dlv_other_items'] = [];
        $dlv_back_other = DlvBack::getOtherDataWithDeliveryID($bac_papa_id)->get();
        if (isset($dlv_back_other) && 0 < count($dlv_back_other)) {
            $rsp_arr['dlv_other_items'] = $dlv_back_other;
        }
        if(Event::order()->value == $delivery->event) {
            $sub_order = SubOrders::where('id', $delivery->event_id)->get()->first();
            $rsp_arr['order_id'] = $sub_order->order_id;
        }

        //退貨商品款式
        $ord_items = [];
        $dlv_back = DlvBack::getDataWithDeliveryID($bac_papa_id)->get();
        if (isset($dlv_back) && 0 < count($dlv_back)) {
            $ord_items = $dlv_back;
        }
        $total_grades = GeneralLedger::total_grade_list();

        $rsp_arr['method'] = 'edit';
        $rsp_arr['delivery'] = $delivery;
        $rsp_arr['bacPapa'] = $bacPapa;
        $rsp_arr['event'] = $delivery->event;
        $rsp_arr['eventId'] = $delivery->event_id;
        $rsp_arr['ord_items'] = $ord_items;
        $rsp_arr['total_grades'] = $total_grades;
        $rsp_arr['formAction'] = Route('cms.delivery.back_store', [
            'bac_papa_id' => $bac_papa_id,
        ], true);
        $rsp_arr['breadcrumb_data'] = ['sn' => $delivery->sn, 'parent' => $delivery->event ];

        return view('cms.commodity.delivery.back', $rsp_arr);
    }

    //銷貨退回明細
    public function back_detail($bac_papa_id)
    {
        $rsp_arr = $this->getBackDetailRsp($bac_papa_id);
        return view('cms.commodity.delivery.back_detail', $rsp_arr);
    }

    public function print_back(Request $request, $bac_papa_id)
    {
        $rsp_arr = $this->getBackDetailRsp($bac_papa_id);
        $rsp_arr['type_display'] = 'back';
        $rsp_arr['user'] = $request->user();
        return view('doc.print_back', $rsp_arr);
    }

    private function getBackDetailRsp($bac_papa_id) {

        $bacPapa = DlvBacPapa::where('id', '=', $bac_papa_id)->first();
        $delivery = Delivery::where('id', '=', $bacPapa->delivery_id)->get()->first();
        if (null == $delivery) {
            return abort(404);
        }
        $item_table = null;
        $dlvBack = null;
        $order = null;
        $orderInvoice = null;
        $logistic = null;
        $rsp_arr['has_payable_data_back'] = false; //退貨付款單已有付款紀錄
        $source_type = null;
        if (Event::order()->value == $delivery->event) {
            $subOrder = SubOrders::where('id', '=', $delivery->event_id)->first();
            $order = Order::orderDetail($subOrder->order_id)->get()->first();
            $rsp_arr['subOrders'] = $subOrder;
            $rsp_arr['order'] = $order;
            $item_table = app(OrderItem::class)->getTable();
            $source_type = app(Order::class)->getTable();
        } else if (Event::consignment()->value == $delivery->event) {
            $order = DB::table(app(Consignment::class)->getTable(). ' as csn')
                ->leftJoin(app(Depot::class)->getTable(). ' as depot', 'depot.id', '=', 'csn.receive_depot_id')
                ->where('csn.id', $delivery->event_id)
                ->whereNull('csn.deleted_at')
                ->select('csn.sn as sn'
                    , 'csn.send_depot_name as sed_name'
                    , 'depot.name as ord_name', 'depot.tel as ord_phone', 'depot.addr as ord_address'
                    , 'depot.name as rec_name', 'depot.tel as rec_phone', 'depot.addr as rec_address'
                )
                ->first();
            $rsp_arr['order'] = $order;
            $item_table = app(ConsignmentItem::class)->getTable();
            $source_type = app(Consignment::class)->getTable();
        } else if (Event::csn_order()->value == $delivery->event) {
            $order = DB::table(app(CsnOrder::class)->getTable(). ' as csn')
                ->leftJoin(app(Depot::class)->getTable(). ' as depot', 'depot.id', '=', 'csn.depot_id')
                ->where('csn.id', $delivery->event_id)
                ->whereNull('csn.deleted_at')
                ->select('csn.sn as sn', 'depot.name as ord_name', 'depot.tel as ord_phone', 'depot.addr as ord_address')
                ->first();
            $rsp_arr['order'] = $order;
            $item_table = app(CsnOrderItem::class)->getTable();
            $source_type = app(CsnOrder::class)->getTable();
        }
        if (null != $source_type) {
            $orderInvoice = OrderInvoice::where('source_type', '=', $source_type)
                ->where('source_id', '=', $delivery->event_id)->first();
            $rsp_arr['orderInvoice'] = $orderInvoice;
        }
        //判斷該付款單是否有付款紀錄
        $paying_order = PayingOrder::where([
            'source_type' => app(Delivery::class)->getTable(),
            'source_id' => $delivery->id,
            'source_sub_id' => null,
            'type' => 9,
            'deleted_at' => null,
            ])
            ->first();
        if (isset($paying_order)) {
            $payable_data = PayingOrder::get_payable_detail($paying_order->id);
            if (0 < count($payable_data)) {
                $rsp_arr['has_payable_data_back'] = true;
            }
        }

        if (isset($item_table)) {
            $dlvBack = DlvBack::getDataWithDeliveryID($bac_papa_id)->get();
        }

        $dlv_other_items = DlvBack::getOtherDataWithDeliveryID($bac_papa_id)->get();

//        $logistic = Logistic::where('id', '=', $delivery->event_id)->first();

        $logistic = DB::table(app(Logistic::class)->getTable(). ' as lgt_tb')
            ->leftJoin(app(ShipmentGroup::class)->getTable(). ' as shi_group', 'shi_group.id', '=', 'lgt_tb.ship_group_id')
            ->select(
                'lgt_tb.*', 'shi_group.name as group_name'
            )
            ->where('lgt_tb.delivery_id', '=', $delivery->id)
            ->first();
        $ord_items_arr = ReceiveDepot::getRcvDepotBackQty($delivery->id, $bac_papa_id, $delivery->event, $delivery->event_id);

        if (isset($ord_items_arr) && 0 < count($ord_items_arr)) {
            $sendBackData = PurchaseLog::getSendBackData($delivery->id, $delivery->event_id);
            //整合退貨入庫時 輸入的說明
            if (isset($sendBackData) && 0 < count(($sendBackData))) {
                foreach ($ord_items_arr as $key => $ord_item) {
                    if (isset($ord_item->receive_depot) && 0 < count($ord_item->receive_depot)) {
                        foreach ($ord_item->receive_depot as $key_rcv_depot => $rcv_depot) {
                            foreach ($sendBackData as $key_send_back => $val_send_back) {
                                if ($rcv_depot->id == $val_send_back->event_id) {
                                    $ord_item->receive_depot[$key_rcv_depot]->memo = $val_send_back->note;
                                }
                            }
                        }
                    }
                }
            }
            //移除數量為空的資料
            foreach ($ord_items_arr as $key => $ord_item) {
                if (isset($ord_item->receive_depot) && 0 < count($ord_item->receive_depot)) {
                    foreach ($ord_item->receive_depot as $key_rcv_depot => $rcv_depot) {
                        if (0 == ($rcv_depot->elebac_qty)) {
                            unset($ord_item->receive_depot[$key_rcv_depot]);
                        }
                    }
                }
                if (isset($ord_item->receive_depot) && 0 == count($ord_item->receive_depot)) {
                    unset($ord_items_arr[$key]);
                }
            }
            $ord_items_arr = json_decode($ord_items_arr);
            $ord_items_arr = array_values((array)$ord_items_arr);
        }

        $elementBackItems = DlvElementBack::where('bac_papa_id', '=', $bac_papa_id)->get();
        $can_edit_back_items = true;
        if (null != $elementBackItems && 0 < count($elementBackItems)) {
            $can_edit_back_items = false;
        }
        $rsp_arr['can_edit_back_items'] = $can_edit_back_items;

        $rsp_arr['logistic'] = $logistic;

        $rsp_arr['event'] = $delivery->event;
        $rsp_arr['delivery'] = $delivery;
        $rsp_arr['bacPapa'] = $bacPapa;
        $rsp_arr['delivery_id'] = $delivery->id;
        $rsp_arr['sn'] = $delivery->sn;
        $rsp_arr['dlvBack'] = $dlvBack;
        $rsp_arr['dlv_other_items'] = $dlv_other_items;
        $rsp_arr['ord_items_arr'] = $ord_items_arr;
        $rsp_arr['breadcrumb_data'] = ['sn' => $delivery->event_sn, 'parent' => $delivery->event ];
        $items = Delivery::delivery_item($delivery->id, 'return', $bac_papa_id)->get();
        foreach ($items as $key => $value) {
            $items[$key]->delivery_items = json_decode($value->delivery_items);
        }
        $rsp_arr['items'] = $items->first();
        $rsp_arr['po_check'] = $source_type == app(Order::class)->getTable() ? PayingOrder::source_confirmation($source_type, $order->id, null, 9) : true;
        return $rsp_arr;
    }

    public function back_inbound($bac_papa_id)
    {
        $bacPapa = DlvBacPapa::where('id', '=', $bac_papa_id)->first();
        // 出貨單號ID
        $delivery = Delivery::where('id', '=', $bacPapa->delivery_id)->first();
        $delivery_id = $delivery->id;
        $event_sn = $delivery->event_sn;
        $rsp_arr = [
            'event' => $delivery->event,
            'eventId' => $delivery->event_id,
        ];

        if(Event::order()->value == $delivery->event) {
            $sub_order = SubOrders::getListWithShiGroupById($delivery->event_id)->get()->first();
            if (null == $sub_order) {
                return abort(404);
            }
            $rsp_arr['order_id'] = $sub_order->order_id;
        } else if(Event::consignment()->value == $delivery->event) {
            $is_all_inbound = true; //是否全部入庫
            $inboundOverviewList = PurchaseInbound::getOverviewInboundList(Event::consignment()->value, $delivery->event_id)->get()->toArray();
            if (null != $inboundOverviewList && 0 < count($inboundOverviewList)) {
                foreach ($inboundOverviewList as $val_ibov) {
                    if (0 != $val_ibov->should_enter_num) {
                        $is_all_inbound = false;
                        break;
                    }
                }
            }
            if (false == $is_all_inbound) {
                return abort(200, '請確認是否全部入庫，或重新選擇出貨數量');
            }

            $consignment = Consignment::where('id', $delivery->event_id)->get()->first();
            $rsp_arr['depot_id'] = $consignment->send_depot_id;
        } else if(Event::csn_order()->value == $delivery->event) {
            $csn_order = CsnOrder::where('id', $delivery->event_id)->get()->first();
            $rsp_arr['depot_id'] = $csn_order->depot_id;
        }
        $ord_items_arr = ReceiveDepot::getRcvDepotBackQty($delivery->id, $bac_papa_id, $delivery->event, $delivery->event_id);

        $rsp_arr['event'] = $delivery->event;
        $rsp_arr['delivery'] = $delivery;
        $rsp_arr['bacPapa'] = $bacPapa;
        $rsp_arr['delivery_id'] = $delivery_id;
        $rsp_arr['sn'] = $delivery->sn;
        $rsp_arr['ord_items_arr'] = $ord_items_arr;
        $rsp_arr['formAction'] = Route('cms.delivery.back_inbound_store', [
            'bac_papa_id' => $bac_papa_id,
        ], true);
        $rsp_arr['breadcrumb_data'] = ['sn' => $event_sn, 'parent' => $delivery->event ];

        return view('cms.commodity.delivery.back_inbound', $rsp_arr);
    }

    public function back_inbound_store(Request $request, int $bac_papa_id)
    {
        $request->validate([
            'id' => 'required',
            'back_qty' => 'required',
            'total_to_back_qty' => 'required',
        ]);

        $items_to_back = $request->only('id', 'back_qty', 'memo', 'total_to_back_qty');
        if (count($items_to_back['id']) != count($items_to_back['back_qty']) && count($items_to_back['id']) != count($items_to_back['memo'])) {
            throw ValidationException::withMessages(['error_msg' => '各資料個數不同']);
        }

        $bacPapa = DlvBacPapa::where('id', '=', $bac_papa_id)->first();
        $delivery = Delivery::where('id', '=', $bacPapa->delivery_id)->get()->first();
        if (null == $delivery) {
            return abort(404);
        }

        //判斷OK後 回寫入各出貨商品的product_style_id prd_type combo_id
        $bdcisc = ReceiveDepot::checkBackDlvComboItemSameCount($delivery->id, $bac_papa_id, $items_to_back);
//        dd($request->all(), $bdcisc);
        if ($bdcisc['success'] == '1') {
            $msg = IttmsDBB::transaction(function () use ($bac_papa_id, $delivery, $bdcisc, $request) {
                //更新狀態
                DlvBacPapa::where('id', '=', $bac_papa_id)->update([
                    'inbound_user_id' => $request->user()->id
                    , 'inbound_user_name' => $request->user()->name
                    , 'inbound_date' => date("Y-m-d H:i:s"),
                ]);
                Delivery::changeBackStatus($delivery->id, BackStatus::add_back_inbound());
                DlvBacPapa::changeBackStatus($bac_papa_id, BackStatus::add_back_inbound());

                $reLFCDS = LogisticFlow::createDeliveryStatus($request->user(), $delivery->id, [LogisticStatus::C3000()]);
                if ($reLFCDS['success'] == 0) {
                    DB::rollBack();
                    return $reLFCDS;
                }
                if (Event::order()->value == $delivery->event) {
                    //狀態須回寫到訂單
                    $subOrder = SubOrders::where('id', '=', $delivery->event_id)->first();
                    OrderFlow::changeOrderStatus($subOrder->order_id, OrderStatus::Backed());
                }

                $is_calc_in_stock = false; //是否計算可售數量
                //出貨只會在同一倉庫出 所以判斷其一元素是理貨倉就需計算可售數量
                for ($num_bdcisc = 0; $num_bdcisc < count($bdcisc['data']['id']); $num_bdcisc++) {
                    if (0 == $bdcisc['data']['depot_id'][$num_bdcisc]) {
                        continue;
                    }
                    //$can_tally = Depot::can_tally($bdcisc['data']['depot_id'][$num_bdcisc]);
                    $can_tally = true;
                    if ($can_tally == true) {
                        $is_calc_in_stock = true;
                        break;
                    }
                }

                if (Event::order()->value == $delivery->event) {
                    if (false == $is_calc_in_stock) {
                        DB::rollBack();
                        return ['success' => 0, 'error_msg' => '無法退貨入庫 該訂單商品含有非理貨倉 出貨之商品'];
                    }
                }

                if (Event::order()->value == $delivery->event || Event::consignment()->value == $delivery->event) {
                    //查找出貨時組出的組合包 相關退貨的商品 將數量加回
                    $dlvBack_combo = DB::table(app(DlvBack::class)->getTable(). ' as dlv_back')
                        ->leftJoin(app(ReceiveDepot::class)->getTable(). ' as rcv_depot', function ($join) {
                            $join->on('rcv_depot.delivery_id', '=', 'dlv_back.delivery_id')
                                ->on('rcv_depot.event_item_id', '=', 'dlv_back.event_item_id')
                                ->where('rcv_depot.prd_type', '=', 'c');
                        })
                        ->leftJoin(app(ProductStyle::class)->getTable(). ' as style', 'style.id', '=', 'dlv_back.product_style_id')
                        ->where('dlv_back.type', DlvBackType::product()->value)
                        ->where('dlv_back.delivery_id', '=', $delivery->id)
                        ->where('dlv_back.bac_papa_id', '=', $bac_papa_id)
                        ->where('style.type', '=', 'c')
                        ->where('dlv_back.qty', '>', 0)
                        ->whereNull('rcv_depot.deleted_at')
                        ->select(
                            'dlv_back.event_item_id'
                            , 'dlv_back.product_style_id'
                            , 'dlv_back.qty'
                            , 'dlv_back.memo'
                            , 'rcv_depot.id as rcv_depot_id'
                        )
                        ->get();
                    if (isset($dlvBack_combo) && 0 < count($dlvBack_combo)
                        && ($is_calc_in_stock)
                    ) {
                        foreach ($dlvBack_combo as $back_item) {
                            $rePSSC = ProductStock::stockChange($back_item->product_style_id, $back_item->qty
                                , StockEvent::send_back()->value, $delivery->event_id
                                , $request->user()->name. ' '. $delivery->sn. ' ' . $back_item->memo);
                            if ($rePSSC['success'] == 0) {
                                DB::rollBack();
                                return $rePSSC;
                            }
                        }
                    }
                    //直接依據退貨數量 寫回出貨單組合包的退貨數量
                    foreach ($dlvBack_combo as $back_item) {
                        ReceiveDepot::where('id', '=', $back_item->rcv_depot_id)->update([
                            'back_qty' => DB::raw("back_qty + $back_item->qty"),
                        ]);
                        if (Event::consignment()->value == $delivery->event) {
                            //組合包 需紀錄退貨紀錄
                            $dcbq = $this->doCsnBackQty($request, $bac_papa_id, $back_item->rcv_depot_id, $back_item->qty);
                            if ($dcbq['success'] == 0) {
                                DB::rollBack();
                                return $dcbq;
                            }
                        }
                    }
                }

                if(isset($bdcisc['data']) && 0 < count($bdcisc['data']) && isset($bdcisc['data']['id']) && 0 < count($bdcisc['data']['id'])) {
                    for ($num_bdcisc = 0; $num_bdcisc < count($bdcisc['data']['id']); $num_bdcisc++) {
                        $rcv_depot_item = new \stdClass();
                        $rcv_depot_item->id = $bdcisc['data']['id'][$num_bdcisc];
                        $rcv_depot_item->back_qty = $bdcisc['data']['back_qty'][$num_bdcisc];
                        $rcv_depot_item->memo = $bdcisc['data']['memo'][$num_bdcisc];
                        $rcv_depot_item->product_style_id = $bdcisc['data']['product_style_id'][$num_bdcisc];
                        $rcv_depot_item->product_title = $bdcisc['data']['product_title'][$num_bdcisc];
                        $rcv_depot_item->inbound_id = $bdcisc['data']['inbound_id'][$num_bdcisc];
                        $rcv_depot_item->prd_type = $bdcisc['data']['prd_type'][$num_bdcisc];
                        $rcv_depot_item->combo_id = $bdcisc['data']['combo_id'][$num_bdcisc];
                        $rcv_depot_item->depot_id = $bdcisc['data']['depot_id'][$num_bdcisc];

                        //增加back_num
                        ReceiveDepot::where('id', $rcv_depot_item->id)->update(['back_qty' => DB::raw("back_qty + $rcv_depot_item->back_qty")]);
                        //加回對應入庫單num
                        $update_arr = [];
                        if (Event::order()->value == $delivery->event) {
                            $update_arr['sale_num'] = DB::raw("sale_num - $rcv_depot_item->back_qty");
                            //TODO 自取可能有入庫 若有入庫過 則需判斷退貨的數量 不得大於後面入庫扣除售出之類的數量
                            // 並須把後面入庫單的退貨數量更新
                            if ('pickup' == $delivery->ship_category) {
                                $pcsInbound_pickup = $this->getPickUpInboundLessThenBackQtyList($rcv_depot_item->id, $rcv_depot_item->back_qty);
                                if (isset($pcsInbound_pickup) && 0 < count($pcsInbound_pickup)) {
                                    //自取有入庫過 不給退貨
                                    DB::rollBack();
                                    return ['success' => 0, 'error_msg' => '訂單自取暫無退貨入庫功能'];
                                }
                            }
                        } else if (Event::consignment()->value == $delivery->event) {
                            $dcbq = $this->doCsnBackQty($request, $bac_papa_id, $rcv_depot_item->id, $rcv_depot_item->back_qty);
                            if ($dcbq['success'] == 0) {
                                DB::rollBack();
                                return $dcbq;
                            }
                            $update_arr['csn_num'] = DB::raw("csn_num - $rcv_depot_item->back_qty");
                        } else if (Event::csn_order()->value == $delivery->event) {
                            $update_arr['sale_num'] = DB::raw("sale_num - $rcv_depot_item->back_qty");
                        }
                        PurchaseInbound::where('id', $rcv_depot_item->inbound_id)->update($update_arr);
                        $ibouund_orignal = PurchaseInbound::where('id', $rcv_depot_item->inbound_id)->first();
                        PcsStatisInbound::updateData($ibouund_orignal->event, $rcv_depot_item->product_style_id, $rcv_depot_item->depot_id, $rcv_depot_item->back_qty);

                        //寫入LOG
                        $rePcsLSC = PurchaseLog::stockChange($delivery->event_id, $rcv_depot_item->product_style_id, $delivery->event, $rcv_depot_item->id
                            , LogEventFeature::send_back()->value, $rcv_depot_item->inbound_id, $rcv_depot_item->back_qty, $rcv_depot_item->memo ?? null
                            , $rcv_depot_item->product_title, $rcv_depot_item->prd_type
                            , $request->user()->id, $request->user()->name, $bac_papa_id);
                        if ($rePcsLSC['success'] == 0) {
                            DB::rollBack();
                            return $rePcsLSC;
                        }
                        $reCreateEB = DlvElementBack::createData($delivery->id, $bac_papa_id, $rcv_depot_item->id, $rcv_depot_item->back_qty, $rcv_depot_item->memo);
                        if ($reCreateEB['success'] == 0) {
                            DB::rollBack();
                            return $reCreateEB;
                        }
                        //將通路庫存加回可售數量 除了寄倉訂購
                        $inboundData = PurchaseInbound::where('id', '=', $rcv_depot_item->inbound_id)->first();
                        if (isset($inboundData)
                            && $is_calc_in_stock
                            && (Event::csn_order()->value != $delivery->event)
                        ) {
                            //若非組合包元素 則需計算可售數量
                            if ('ce' != $rcv_depot_item->prd_type) {
                                $memo = $rcv_depot_item->memo ?? '';
                                //$ibdata_can_tally = $inboundData->can_tally;
                                $ibdata_can_tally = true;
                                $rePSSC = ProductStock::stockChange($inboundData->product_style_id, $rcv_depot_item->back_qty
                                    , StockEvent::send_back()->value, $delivery->event_id
                                    , $request->user()->name. ' '. $delivery->sn. ' ' . $memo
                                    , false, $ibdata_can_tally);
                                if ($rePSSC['success'] == 0) {
                                    DB::rollBack();
                                    return $rePSSC;
                                }
                            }
                        }
                    }
                }
                return ['success' => 1];
            });
            if ($msg['success'] == 0) {
                throw ValidationException::withMessages(['error_msg' => $msg['error_msg']]);
            } else {
                wToast('儲存成功');
                return redirect(Route('cms.delivery.back_detail', [
                    'bac_papa_id' => $bac_papa_id,
                ], true));
            }
        } else {
            throw ValidationException::withMessages(['error_msg' => $bdcisc['error_msg']]);
        }
    }

    private function doCsnBackQty($request, $bac_papa_id, $event_item_id, $back_qty) {
        $msg = IttmsDBB::transaction(function () use ($request, $bac_papa_id, $event_item_id, $back_qty) {
            //TODO 寄倉可能有入庫 若有入庫過 須一併先把那邊的入庫退貨
            //找到該筆出貨資料的寄倉入庫單
            // 若有入庫過 需更新back_num
            $param = ['event' => Event::consignment()->value
                , 'event_item_id' => $event_item_id
            ];
            $inboundList_consignment = PurchaseInbound::getInboundListWithEventSn(app(Consignment::class)->getTable(), [Event::consignment()->value], $param, false);
            $inboundList_consignment = $inboundList_consignment->get();
            if (0 != $back_qty && isset($inboundList_consignment) && 0 < count($inboundList_consignment)) {
                $curr_calc_back_qty = $back_qty;
                foreach ($inboundList_consignment as $key => $inbound_item) {
                    //判斷該寄倉入庫單可退回的數量 <= 總數量-售出數量-back_num
                    $inbound = PurchaseInbound::where('id', $inbound_item->inbound_id)->first();
                    $todo_qty = 0;
                    $LogEventFeature = null;
                    if (0 < $back_qty) {
                        //扣除 總數量 > 0
                        $qty = $inbound->inbound_num - $inbound->sale_num - $inbound->csn_num - $inbound->consume_num - $inbound->back_num - $inbound->scrap_num;

                        $LogEventFeature = LogEventFeature::send_back_from_rcv()->value;
                        //未扣完 則扣目前總量 剩餘的往下一筆繼續
                        if (0 > ($qty - $curr_calc_back_qty)) {
                            $todo_qty = $qty;
                            $curr_calc_back_qty = $curr_calc_back_qty - $qty;
                        } else {
                            $todo_qty = $curr_calc_back_qty;
                            $curr_calc_back_qty = 0;
                        }
                    } else if (0 > $back_qty && 0 < $inbound->back_num) {
                        //加回 判斷back_num > 0

                        $LogEventFeature = LogEventFeature::send_back_cancle_from_rcv()->value;
                        //未加完 則加目前總量 剩餘的往下一筆繼續
                        if (0 > ($inbound->back_num + $curr_calc_back_qty)) {
                            $todo_qty = ($inbound->back_num * -1);
                            $curr_calc_back_qty = $curr_calc_back_qty + $inbound->back_num;
                        } else {
                            $todo_qty = $curr_calc_back_qty;
                            $curr_calc_back_qty = 0;
                        }
                    }
                    if (0 != $todo_qty) {
                        $reUBI = PurchaseInbound::updateBackInbound($inbound_item, Event::consignment()->value, $LogEventFeature, $todo_qty, $bac_papa_id);
                        if ($reUBI['success'] == 0) {
                            DB::rollBack();
                            return $reUBI;
                        }
                    }
                }
                if (0 != $curr_calc_back_qty) {
                    $inbound_sns = [];
                    if (null != $inboundList_consignment && 0 < count($inboundList_consignment)) {
                        foreach($inboundList_consignment as $val_ib) {
                            $inbound_sns[] = $val_ib->inbound_sn;
                        }

                    }
                    return ['success' => 0
                        , 'error_msg' => '當前庫存總數無法進行退貨，請確認剩餘數量是否可執行 '
                        . '; $bac_papa_id:'. $bac_papa_id. '; $event_item_id:'. $event_item_id. '; $back_qty:'. $back_qty. '; inbound_sn:'. implode(',', $inbound_sns)
                    ];
                }
            }
            return ['success' => 1, 'error_msg' => ''];
        });
        return $msg;
    }

    //找自取倉已入庫 且數量小於欲退數量資料
    private function getPickUpInboundLessThenBackQtyList($rcv_depot_id, $back_qty)
    {
        $pcsInbound_sub = DB::table(app(PurchaseInbound::class)->getTable(). ' as inbound')
            ->where('inbound.event', '=', Event::ord_pickup()->value)
            ->where('inbound.event_id', '=', $rcv_depot_id)
            ->whereNull('inbound.deleted_at')
            ->select(
                'inbound.event'
                , 'inbound.event_id'
                , 'inbound.product_style_id'
                , DB::raw('(sum(inbound.inbound_num) - sum(inbound.sale_num) - sum(inbound.csn_num) - sum(inbound.consume_num) - sum(inbound.back_num) - sum(inbound.scrap_num)) as total_qty')
            )
            ->groupBy('inbound.event')
            ->groupBy('inbound.event_id')
            ->groupBy('inbound.product_style_id');
        $pcsInbound_pickup = DB::query()->fromSub($pcsInbound_sub, 'sub_tb')
            ->where('sub_tb.total_qty', '<', $back_qty)
            ->get();
        return $pcsInbound_pickup;
    }

    public function back_inbound_delete(Request $request, int $bac_papa_id)
    {
//        dd('back_inbound_delete', $request->all(), $bac_papa_id);
        $bacPapa = DlvBacPapa::where('id', '=', $bac_papa_id)->first();
        $delivery = Delivery::where('id', '=', $bacPapa->delivery_id)->get()->first();
        if (false == isset($delivery) || false == isset($bacPapa->inbound_date)) {
            return abort(404);
        }
        $elementBackItems = DB::table(app(DlvElementBack::class)->getTable(). ' as elebac')
            ->leftJoin(app(ReceiveDepot::class)->getTable(). ' as rcv_depot', function ($join) use($bac_papa_id) {
                $join->on('elebac.rcv_depot_id', '=', 'rcv_depot.id');
            })
            ->where('elebac.bac_papa_id', '=', $bac_papa_id)
            ->select(
                'elebac.id as elebac_id'
                , 'elebac.rcv_depot_id as rcv_depot_id'
                , 'elebac.qty as back_qty'
                , 'rcv_depot.inbound_id'
                , 'rcv_depot.product_style_id'
                , 'rcv_depot.prd_type'
                , 'rcv_depot.depot_id'
                , 'rcv_depot.product_title'
            )
            ->get();
        if (isset($elementBackItems) && 0 < count($elementBackItems)) {
            $msg = IttmsDBB::transaction(function () use ($bac_papa_id, $delivery, $elementBackItems, $request) {
                DlvBacPapa::where('id', $bac_papa_id)->update([
                    'inbound_user_id' => null
                    , 'inbound_user_name' => null
                    , 'inbound_date' => null
                ]);
                Delivery::changeBackStatus($delivery->id, BackStatus::del_back_inbound());
                DlvBacPapa::changeBackStatus($bac_papa_id, BackStatus::del_back_inbound());
                $reLFCDS = LogisticFlow::createDeliveryStatus($request->user(), $delivery->id, [LogisticStatus::C2000()]);
                if ($reLFCDS['success'] == 0) {
                    DB::rollBack();
                    return $reLFCDS;
                }
                if (Event::order()->value == $delivery->event) {
                    //狀態須回寫到訂單
                    $subOrder = SubOrders::where('id', '=', $delivery->event_id)->first();
                    OrderFlow::changeOrderStatus($subOrder->order_id, OrderStatus::CancleBack());
                }
                $is_calc_in_stock = false; //是否計算可售數量
                //出貨只會在同一倉庫出 所以判斷其一元素是理貨倉就需計算可售數量
                foreach ($elementBackItems as $key_rcv => $val_rcv) {
                    if (0 == $val_rcv->depot_id) {
                        continue;
                    }
                    //$can_tally = Depot::can_tally($val_rcv->depot_id);
                    $can_tally = true;
                    if ($can_tally == true) {
                        $is_calc_in_stock = true;
                        break;
                    }
                }

                if (Event::order()->value == $delivery->event || Event::consignment()->value == $delivery->event) {
                    //查找出貨時組出的組合包 相關退貨的商品 將數量加回
                    $dlvBack_combo = DB::table(app(DlvBack::class)->getTable(). ' as dlv_back')
                        ->leftJoin(app(ReceiveDepot::class)->getTable(). ' as rcv_depot', function ($join) {
                            $join->on('rcv_depot.delivery_id', '=', 'dlv_back.delivery_id')
                                ->on('rcv_depot.event_item_id', '=', 'dlv_back.event_item_id')
                                ->where('rcv_depot.prd_type', '=', 'c');
                        })
                        ->leftJoin(app(ProductStyle::class)->getTable(). ' as style', 'style.id', '=', 'dlv_back.product_style_id')
                        ->where('dlv_back.type', DlvBackType::product()->value)
                        ->where('dlv_back.delivery_id', '=', $delivery->id)
                        ->where('dlv_back.bac_papa_id', '=', $bac_papa_id)
                        ->where('style.type', '=', 'c')
                        ->where('dlv_back.qty', '>', 0)
                        ->whereNull('rcv_depot.deleted_at')
                        ->select(
                            'dlv_back.event_item_id'
                            , 'dlv_back.product_style_id'
                            , 'dlv_back.qty'
                            , 'dlv_back.memo'
                            , 'rcv_depot.id as rcv_depot_id'
                        )
                        ->get();
                    if (isset($dlvBack_combo) && 0 < count($dlvBack_combo)
                        && ($is_calc_in_stock)
                    ) {
                        foreach ($dlvBack_combo as $back_item) {

                            $rePSSC = ProductStock::stockChange($back_item->product_style_id, $back_item->qty * -1
                                , StockEvent::send_back_cancle()->value, $delivery->event_id
                                , $request->user()->name. ' '. $delivery->sn. ' ' . $back_item->memo);
                            if ($rePSSC['success'] == 0) {
                                DB::rollBack();
                                return $rePSSC;
                            }
                        }
                    }
                    foreach ($dlvBack_combo as $back_item) {
                        //找出組合包商品 減少back_num
                        ReceiveDepot::where('id', '=', $back_item->rcv_depot_id)->update([
                            'back_qty' => DB::raw("back_qty + $back_item->qty * -1"),
                        ]);
                        if (Event::consignment()->value == $delivery->event) {
                            //組合包 需紀錄退貨紀錄
                            $dcbq = $this->doCsnBackQty($request, $bac_papa_id, $back_item->rcv_depot_id, $back_item->qty * -1);
                            if ($dcbq['success'] == 0) {
                                DB::rollBack();
                                return $dcbq;
                            }
                        }
                    }
                }

                foreach ($elementBackItems as $key_rcv => $val_rcv) {
                    //減少back_num
                    ReceiveDepot::where('id', $val_rcv->rcv_depot_id)->update(['back_qty' => DB::raw("back_qty - $val_rcv->back_qty")]);
                    //減回對應入庫單num
                    $update_arr = [];
                    if (Event::order()->value == $delivery->event) {
                        $update_arr['sale_num'] = DB::raw("sale_num + $val_rcv->back_qty");
                        //TODO 自取可能有入庫 若有入庫過 則需判斷退貨的數量 不得大於後面入庫扣除售出之類的數量
                        // 並須把後面入庫單的退貨數量更新
                        if ('pickup' == $delivery->ship_category) {
                            $pcsInbound_pickup = $this->getPickUpInboundLessThenBackQtyList($val_rcv->rcv_depot_id, $val_rcv->back_qty);
                            if (isset($pcsInbound_pickup) && 0 < count($pcsInbound_pickup)) {
                                //自取有入庫過 不給退貨
                                DB::rollBack();
                                return ['success' => 0, 'error_msg' => '訂單自取暫無退貨入庫功能'];
                            }
                        }
                    } else if (Event::consignment()->value == $delivery->event) {
                        $dcbq = $this->doCsnBackQty($request, $bac_papa_id, $val_rcv->rcv_depot_id, $val_rcv->back_qty * -1);
                        if ($dcbq['success'] == 0) {
                            DB::rollBack();
                            return $dcbq;
                        }
                        $update_arr['csn_num'] = DB::raw("csn_num + $val_rcv->back_qty");
                    } else if (Event::csn_order()->value == $delivery->event) {
                        $update_arr['sale_num'] = DB::raw("sale_num + $val_rcv->back_qty");
                    }
                    PurchaseInbound::where('id', $val_rcv->inbound_id)->update($update_arr);
                    $ibouund_orignal = PurchaseInbound::where('id', $val_rcv->inbound_id)->first();
                    PcsStatisInbound::updateData($ibouund_orignal->event, $val_rcv->product_style_id, $val_rcv->depot_id, $val_rcv->back_qty * -1);

                    //寫入LOG
                    $rePcsLSC = PurchaseLog::stockChange($delivery->event_id, $val_rcv->product_style_id, $delivery->event, $val_rcv->rcv_depot_id
                        , LogEventFeature::send_back_cancle()->value, $val_rcv->inbound_id, $val_rcv->back_qty * -1, $val_rcv->memo ?? null
                        , $val_rcv->product_title, $val_rcv->prd_type
                        , $request->user()->id, $request->user()->name, $bac_papa_id);
                    if ($rePcsLSC['success'] == 0) {
                        DB::rollBack();
                        return $rePcsLSC;
                    }

                    //將通路庫存減回可售數量 除了寄倉訂購
                    $inboundData = PurchaseInbound::where('id', '=', $val_rcv->inbound_id)->first();
                    if (isset($inboundData)
                        && $is_calc_in_stock
                        && (Event::csn_order()->value != $delivery->event)
                    ) {
                        //若非組合包元素 則需計算可售數量
                        if ('ce' != $val_rcv->prd_type) {
                            $memo = '';
                            //$ibdata_can_tally = $inboundData->can_tally;
                            $ibdata_can_tally = true;
                            $rePSSC = ProductStock::stockChange($inboundData->product_style_id, $val_rcv->back_qty * -1
                                , StockEvent::send_back_cancle()->value, $delivery->event_id
                                , $request->user()->name. ' '. $delivery->sn. ' ' . $memo
                                , false, $ibdata_can_tally);
                            if ($rePSSC['success'] == 0) {
                                DB::rollBack();
                                return $rePSSC;
                            }
                        }
                    }
                }
                DlvElementBack::where('bac_papa_id', $bac_papa_id)->delete();

                return ['success' => 1];
            });
            if ($msg['success'] == 0) {
                wToast($msg['error_msg'], ['type'=>'danger']);
                throw ValidationException::withMessages(['error_msg' => $msg['error_msg']]);
            } else {
                wToast('儲存成功');
                return redirect()->back()->withInput();
            }
        } else {
            throw ValidationException::withMessages(['error_msg' => '無可退貨入庫數量']);
        }
    }

    public function roe_po(Request $request, $id, $behavior, $bac_papa_id = null)
    {
        $request->merge([
            'id' => $id,
            'behavior' => $behavior,
            'bac_papa_id' => $bac_papa_id,
        ]);

        $request->validate([
            'id' => 'required|exists:dlv_delivery,id',
            'behavior' => 'required|in:return,out,exchange',
            'bac_papa_id' => 'nullable|exists:dlv_bac_papa,id',
        ]);

        $source_type = app(Delivery::class)->getTable();
        if($behavior == 'return'){
            $type = 9;
        } else if($behavior == 'out'){
            $type = 8;
        } else if($behavior == 'exchange'){
            $type = 7;
        }

        $paying_order = PayingOrder::where([
            'source_type' => $source_type,
            'source_id' => $id,
            'source_sub_id' => $bac_papa_id,
            'type' => $type,
            'deleted_at' => null,
        ])->first();

        $delivery = Delivery::delivery_item($id, $behavior, $bac_papa_id)->get();
        foreach ($delivery as $key => $value) {
            $delivery[$key]->delivery_items = json_decode($value->delivery_items);
        }
        $delivery = $delivery->first();

        if (!$paying_order) {
            $product_grade = ReceivedDefault::where('name', '=', 'product')->first()->default_grade_id;
            $logistics_grade = ReceivedDefault::where('name', '=', 'logistics')->first()->default_grade_id;

            $result = PayingOrder::createPayingOrder(
                $source_type,
                $id,
                $bac_papa_id,
                $request->user()->id,
                $type,
                $product_grade,
                $logistics_grade,
                $delivery->delivery_total_price ?? 0,
                '',
                '',
                $delivery->buyer_id,
                $delivery->buyer_name,
                $delivery->buyer_phone,
                $delivery->buyer_address
            );

            $paying_order = PayingOrder::findOrFail($result['id']);

            $delivery = Delivery::delivery_item($id, $behavior, $bac_papa_id)->get();
            foreach ($delivery as $key => $value) {
                $delivery[$key]->delivery_items = json_decode($value->delivery_items);
            }
            $delivery = $delivery->first();
        }

        $applied_company = DB::table('acc_company')->where('id', 1)->first();

        // $order_discount = DB::table('ord_discounts')->where([
        //         'order_type'=>'main',
        //         'order_id'=>request('id'),
        //     ])->where('discount_value', '>', 0)->get()->toArray();
        // foreach($order_discount as $value){
        //     $value->account_code = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->code : '4000';
        //     $value->account_name = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->name : '無設定會計科目';
        // }

        $payable_data = PayingOrder::get_payable_detail($paying_order->id);
        $data_status_check = PayingOrder::payable_data_status_check($payable_data);

        $accountant = User::whereIn('id', $payable_data->pluck('accountant_id_fk')->toArray())->get();
        $accountant = array_unique($accountant->pluck('name')->toArray());
        asort($accountant);

        $undertaker = User::find($paying_order->usr_users_id);

        $zh_price = num_to_str($paying_order->price);

        if($paying_order && $paying_order->append_po_id){
            $append_po = PayingOrder::find($paying_order->append_po_id);
            $paying_order->append_po_link = PayingOrder::paying_order_link($append_po->source_type, $append_po->source_id, $append_po->source_sub_id, $append_po->type);
        }

        $view = 'cms.commodity.delivery.roe_po';
        if (request('action') == 'print') {
            $view = 'doc.print_delivery_roe_po';
        }

        return view($view, [
            'breadcrumb_data' => [
                'sn' => $delivery->delivery_event_sn,
                'behavior' => $behavior,
                'po_link' => $delivery->po_link,
                'po_source_link' => $delivery->po_source_link,
            ],
            'behavior' => $behavior,
            'paying_order' => $paying_order,
            'payable_data' => $payable_data,
            'data_status_check' => $data_status_check,
            'delivery' => $delivery,
            // 'order_discount' => $order_discount,
            'applied_company' => $applied_company,
            'accountant'=>implode(',', $accountant),
            'undertaker' => $undertaker,
            'zh_price' => $zh_price,
            'relation_order' => Petition::getBindedOrder($paying_order->id, 'ISG'),
        ]);
    }

    public function roe_po_create(Request $request, $id, $behavior, $bac_papa_id = null)
    {
        $request->merge([
            'id' => $id,
            'behavior' => $behavior,
            'bac_papa_id' => $bac_papa_id,
        ]);

        $request->validate([
            'id' => 'required|exists:dlv_delivery,id',
            'behavior' => 'required|in:return,out,exchange',
            'bac_papa_id' => 'nullable|exists:dlv_bac_papa,id',
        ]);

        $source_type = app(Delivery::class)->getTable();
        if($behavior == 'return'){
            $type = 9;
        } else if($behavior == 'out'){
            $type = 8;
        } else if($behavior == 'exchange'){
            $type = 7;
        }

        $paying_order = PayingOrder::where([
            'source_type' => $source_type,
            'source_id' => $id,
            'source_sub_id' => $bac_papa_id,
            'type' => $type,
            'deleted_at' => null,
        ])->first();

        if(! $paying_order) {
            return abort(404);
        }

        $delivery = Delivery::delivery_item($id, $behavior, $bac_papa_id)->get();
        foreach ($delivery as $key => $value) {
            $delivery[$key]->delivery_items = json_decode($value->delivery_items);
        }
        $delivery = $delivery->first();

        if($request->isMethod('post')){
            $request->merge([
                'pay_order_id'=>$paying_order->id,
            ]);

            $request->validate([
                'acc_transact_type_fk' => 'required|regex:/^[1-6]$/',
            ]);

            $req = $request->all();

            $payable_type = $req['acc_transact_type_fk'];

            switch ($payable_type) {
                case Payment::Cash:
                    PayableCash::storePayableCash($req);
                    break;
                case Payment::Cheque:
                    $request->validate([
                        'cheque.ticket_number' => 'required|unique:acc_payable_cheque,ticket_number,po_delete,status_code|regex:/^[A-Z]{2}[0-9]{7}$/',
                    ]);
                    PayableCheque::storePayableCheque($req);
                    break;
                case Payment::Remittance:
                    PayableRemit::storePayableRemit($req);
                    break;
                case Payment::ForeignCurrency:
                    PayableForeignCurrency::storePayableCurrency($req);
                    break;
                case Payment::AccountsPayable:
                    PayableAccount::storePayablePayableAccount($req);
                    break;
                case Payment::Other:
                    PayableOther::storePayableOther($req);
                    break;
            }

            $payable_data = PayingOrder::get_payable_detail($paying_order->id);
            if (count($payable_data) > 0 && $paying_order->price == $payable_data->sum('tw_price')) {
                $paying_order->update([
                    'balance_date' => date('Y-m-d H:i:s'),
                    'payment_date' => $req['payment_date'],
                ]);

                DayEnd::match_day_end_status($req['payment_date'], $paying_order->sn);
            }

            if (PayingOrder::find($paying_order->id) && PayingOrder::find($paying_order->id)->balance_date) {
                return redirect($delivery->po_link);

            } else {
                return redirect()->route('cms.delivery.roe-po-create', [
                    'id' => $delivery->delivery_id,
                    'behavior' => $behavior,
                    'bac_papa_id' => $bac_papa_id,
                ]);
            }

        } else {

            if($paying_order->balance_date) {
                return abort(404);
            }

            // $order_discount = DB::table('ord_discounts')->where([
            //         'order_type'=>'main',
            //         'order_id'=>request('id'),
            //     ])->where('discount_value', '>', 0)->get()->toArray();
            // foreach($order_discount as $value){
            //     $value->account_code = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->code : '4000';
            //     $value->account_name = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->name : '無設定會計科目';
            // }

            $currency = DB::table('acc_currency')->find($paying_order->acc_currency_fk);
            if(!$currency){
                $currency = (object)[
                    'name'=>'NTD',
                    'rate'=>1,
                ];
            }

            $payable_data = PayingOrder::get_payable_detail($paying_order->id);

            $tw_price = $paying_order->price - $payable_data->sum('tw_price');

            $total_grades = GeneralLedger::total_grade_list();

            return view('cms.commodity.delivery.roe_po_create', [
                'breadcrumb_data' => [
                    'sn' => $delivery->delivery_event_sn,
                    'behavior' => $behavior,
                    'po_link' => $delivery->po_link,
                    'po_source_link' => $delivery->po_source_link,
                ],
                'behavior' => $behavior,
                'paying_order' => $paying_order,
                'payable_data' => $payable_data,
                'delivery' => $delivery,
                // 'order_discount' => $order_discount,
                'currency' => $currency,
                'tw_price' => $tw_price,
                'total_grades' => $total_grades,

                'cashDefault' => PayableDefault::where('name', 'cash')->pluck('default_grade_id')->toArray(),
                'chequeDefault' => PayableDefault::where('name', 'cheque')->pluck('default_grade_id')->toArray(),
                'remitDefault' => PayableDefault::where('name', 'remittance')->pluck('default_grade_id')->toArray(),
                'all_currency' => PayableDefault::getCurrencyOptionData()['selectedCurrencyResult']->toArray(),
                'currencyDefault' => PayableDefault::where('name', 'foreign_currency')->pluck('default_grade_id')->toArray(),
                'accountPayableDefault' => PayableDefault::where('name', 'accounts_payable')->pluck('default_grade_id')->toArray(),
                'otherDefault' => PayableDefault::where('name', 'other')->pluck('default_grade_id')->toArray(),

                'form_action' => route('cms.delivery.roe-po-create', ['id' => $delivery->delivery_id, 'behavior' => $behavior, 'bac_papa_id' => $bac_papa_id]),
                'transactTypeList' => AccountPayable::getTransactTypeList(),
                'chequeStatus' => ChequeStatus::get_key_value(),
            ]);
        }
    }
}

