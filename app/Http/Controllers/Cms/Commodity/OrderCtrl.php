<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Discount\DividendCategory;
use App\Enums\Delivery\Event;
use App\Enums\Order\UserAddrType;
use App\Http\Controllers\Controller;
use App\Models\Addr;
use App\Models\Customer;
use App\Models\Depot;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderCart;
use App\Models\CustomerDividend;
use App\Models\OrderStatus;
use App\Models\PurchaseInbound;
use App\Models\ReceiveDepot;
use App\Models\ReceivedOrder;
use App\Models\SaleChannel;
use App\Models\ShipmentStatus;
use App\Models\UserSalechannel;
use App\Models\AccountPayable;
use App\Models\PayingOrder;
use App\Models\PayableDefault;
use App\Models\AllGrade;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        // Order::createOrder([]);
        //   dd(Discount::getDiscountStatus(1));
        // dd(Order::orderList()->get()->toArray());

        $query = $request->query();
        //   dd($query);
        $cond = [];
        $page = getPageCount(Arr::get($query, 'data_per_page', 10));
        $cond['keyword'] = Arr::get($query, 'keyword', null);
        $cond['order_status'] = Arr::get($query, 'order_status', []);
        $cond['shipment_status'] = Arr::get($query, 'shipment_status', []);
        $cond['sale_channel_id'] = Arr::get($query, 'sale_channel_id', []);
        $cond['order_sdate'] = Arr::get($query, 'order_sdate', null);
        $cond['order_edate'] = Arr::get($query, 'order_edate', null);

        $order_date = null;
        if ($cond['order_sdate'] && $cond['order_edate']) {
            $order_date = [$cond['order_sdate'], $cond['order_edate']];
        }

        if (gettype($cond['order_status']) == 'string') {
            $cond['order_status'] = explode(',', $cond['order_status']);
        } else {
            $cond['order_status'] = [];
        }

        if (gettype($cond['shipment_status']) == 'string') {
            $cond['shipment_status'] = explode(',', $cond['shipment_status']);
        } else {
            $cond['shipment_status'] = [];
        }

        $dataList = Order::orderList($cond['keyword'], $cond['order_status'], $cond['sale_channel_id'], $order_date)
            ->paginate($page)->appends($query);

        // dd(OrderStatus::select('code as id','title')->toBase()->get()->toArray());
        return view('cms.commodity.order.list', [
            'dataList' => $dataList,
            'cond' => $cond,
            'orderStatus' => OrderStatus::select('code as id', 'title')->toBase()->get(),
            'shipmentStatus' => ShipmentStatus::select('code as id', 'title')->toBase()->get(),
            'saleChannels' => SaleChannel::select('id', 'title')->get()->toArray(),
            'data_per_page' => $page]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        //   dd(Discount::checkCode('fkfk',[1,2,4]));
        $cart = null;
        if (old('product_style_id')) {
            $oldData = [];
            foreach (old('product_style_id') as $key => $v) {
                $oldData[] = [
                    'product_id' => old('product_id')[$key],
                    'product_style_id' => $v,
                    'shipment_type' => old('shipment_type')[$key],
                    'shipment_event_id' => old('shipment_event_id')[$key],
                    'qty' => old('qty')[$key],
                ];
            }

            $cart = OrderCart::cartFormater($oldData, old('salechannel_id'), null, false);
            if ($cart['success'] != 1) {
                dd($cart);
            }

        }

        $overbought_id = old('overbought_id');

        $regions = [
            'sed' => [],
            'ord' => [],
            'rec' => [],
        ];

        if (old('sed_city_id')) {
            $regions['sed'] = Addr::getRegions(old('sed_city_id'));
        }
        if (old('ord_city_id')) {
            $regions['ord'] = Addr::getRegions(old('ord_city_id'));
        }
        if (old('rec_city_id')) {
            $regions['rec'] = Addr::getRegions(old('rec_city_id'));
        }

        $customer_id = $request->user()->customer_id;
        if ($customer_id) {
            $salechannels = UserSalechannel::getSalechannels($request->user()->id)->get()->toArray();
        } else {
            $salechannels = [];
        }

        $citys = Addr::getCitys();

        //  dd(Discount::getDiscounts('global-normal'));
        //    dd($citys);
        return view('cms.commodity.order.edit', [
            'customer_id' => $customer_id,
            'customers' => Customer::where('id', $customer_id)->get(),
            'citys' => $citys,
            'cart' => $cart,
            'regions' => $regions,
            'overbought_id' => $overbought_id,
            'salechannels' => $salechannels,
            'discounts' => Discount::getDiscounts('global-normal'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $arrVali = [];
        foreach (UserAddrType::asArray() as $value) {
            switch ($value) {
                case 'receiver':
                    $prefix = 'rec';
                    break;
                case 'orderer':
                    $prefix = 'ord';
                    break;
                case 'sender':
                    $prefix = 'sed';
                    break;
            }

            $arrVali[$prefix . '_name'] = 'required';
            $arrVali[$prefix . '_phone'] = 'required';
            $arrVali[$prefix . '_city_id'] = 'required';
            $arrVali[$prefix . '_region_id'] = 'required';
            $arrVali[$prefix . '_addr'] = 'required';
            $address[$prefix . '_address'] = 'required';

        }

        $request->validate(array_merge([
            'customer_id' => 'required',
            'product_id' => 'required|array',
            'product_style_id' => 'required|array',
            'shipment_type' => 'required|array',
            'shipment_event_id' => 'required|array',
            'salechannel_id' => 'required',
        ], $arrVali));

        $d = $request->all();

        $customer = Customer::where('id', $d['customer_id'])->get()->first();

        $items = [];
        foreach ($d['product_style_id'] as $key => $product_style_id) {
            $items[] = ['product_id' => $d['product_id'][$key],
                'product_style_id' => $product_style_id,
                'qty' => $d['qty'][$key],
                'shipment_type' => $d['shipment_type'][$key],
                'shipment_event_id' => $d['shipment_event_id'][$key]];
        }

        $address = [];
        foreach (UserAddrType::asArray() as $value) {
            switch ($value) {
                case 'receiver':
                    $prefix = 'rec';
                    break;
                case 'orderer':
                    $prefix = 'ord';
                    break;
                case 'sender':
                    $prefix = 'sed';
                    break;
            }

            $address[] = ['name' => $d[$prefix . '_name'], 'phone' => $d[$prefix . '_phone'], 'address' => $d[$prefix . '_address'], 'type' => $value];

        }

        $coupon = null;
        if (isset($d['coupon_type']) && isset($d['coupon_sn'])) {
            $coupon = [$d['coupon_type'], $d['coupon_sn']];
        }

        $re = Order::createOrder($customer->email, $d['salechannel_id'], $address, $items, $d['note'], $coupon);
        if ($re['success'] == '1') {
            wToast('訂單新增成功');
            return redirect(route('cms.order.detail', [
                'id' => $re['order_id'],
            ]));
        }
        $errors = [];
        $addInput = [];
        if (isset($re['event'])) {
            switch ($re['event']) {
                case "address":
                    switch ($re['event_id']) {
                        case UserAddrType::orderer()->value:
                            $errors['ord_address'] = "格式錯誤";
                            break;
                        case UserAddrType::receiver()->value:
                            $errors['rec_address'] = "格式錯誤";
                            break;
                        case UserAddrType::sender()->value:
                            $errors['sed_address'] = "格式錯誤";
                            break;
                        default:
                            $errors['record'] = "格式錯誤";
                    }
                    break;
                case "product":
                    $addInput['overbought_id'] = $re['event_id'];
                    break;
                case "coupon":
                    $errors['coupon'] = $re['error_msg'];
                    break;
            }
        }

        return redirect()->back()->withInput(array_merge($request->input(), $addInput))->withErrors($errors);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the data for order detail.
     *
     * @param  int  $id
     * @param  int  $subOrderId 若有值 則只顯示該子訂單
     * @return \Illuminate\Http\Response
     */
    public function detail($id, $subOrderId = null)
    {
        $order = Order::orderDetail($id)->get()->first();
        $subOrder = Order::subOrderDetail($id, $subOrderId, true)->get()->toArray();

        //  dd(Discount::orderDiscountList('main',$id)->get()->toArray());

        foreach ($subOrder as $key => $value) {
            $subOrder[$key]->items = json_decode($value->items);
            $subOrder[$key]->consume_items = json_decode($value->consume_items);
        }

        //    dd($order);

        if (!$order) {
            return abort(404);
        }
        //  dd( Discount::orderDiscountList('main', $id)->get()->toArray());

        $sn = $order->sn;

        $receivable = false;
        $received_order_collection = ReceivedOrder::where([
            'order_id' => $id,
            'deleted_at' => null,
        ]);
        $received_order_data = $received_order_collection->first();
        if ($received_order_data && $received_order_data->balance_date) {
            $receivable = true;
        }

        $dividend = CustomerDividend::where('category', DividendCategory::Order())
            ->where('category_sn', $order->sn)->get()->first();

       
        if ($dividend) {
            $dividend = $dividend->dividend;
        } else {
            $dividend = 0;
        }   

        
        return view('cms.commodity.order.detail', [
            'sn' => $sn,
            'order' => $order,
            'subOrders' => $subOrder,
            'breadcrumb_data' => $sn,
            'subOrderId' => $subOrderId,
            'discounts' => Discount::orderDiscountList('main', $id)->get()->toArray(),
            'receivable' => $receivable,
            'received_order_data' => $received_order_data,
            'dividend'=>$dividend
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function inbound(Request $request, $subOrderId) {
        $sub_order = DB::table('ord_sub_orders as sub_order')
            ->leftJoin('dlv_delivery as delivery', function ($join) {
                $join->on('delivery.event_id', '=', 'sub_order.id')
                    ->where('delivery.event', '=', Event::order()->value);
            })
            ->select(
                'sub_order.id as id'
                , 'sub_order.order_id as order_id'
                , 'sub_order.sn as sn'
                , 'sub_order.ship_category as ship_category'
                , 'delivery.audit_date'
                , 'sub_order.ship_event_id as depot_id'
            )
            ->get()->first()
            ;

        if (!$sub_order || 'pickup' != $sub_order->ship_category) {
            return abort(404);
        }
        $purchaseItemList = ReceiveDepot::getShouldEnterNumDataList(Event::order()->value, $subOrderId);


        $inboundList = PurchaseInbound::getInboundList(['event' => Event::ord_pickup()->value, 'purchase_id' => $subOrderId])
            ->orderByDesc('inbound.created_at')
            ->get()->toArray();
        $inboundOverviewList = PurchaseInbound::getOverviewInboundList(Event::ord_pickup()->value, $subOrderId)->get()->toArray();

//        dd(123, $subOrderId, $purchaseItemList);

        $depotList = Depot::all()->toArray();
        return view('cms.commodity.order.inbound', [
            'purchaseData' => $sub_order,
            'send_depot_id' => $sub_order->depot_id,
            'purchaseItemList' => $purchaseItemList->get(),
            'inboundList' => $inboundList,
            'inboundOverviewList' => $inboundOverviewList,
            'depotList' => $depotList,
            'formAction' => Route('cms.order.store_inbound', ['id' => $subOrderId,]),
            //'formActionClose' => Route('cms.order.close', ['id' => $subOrderId,]),
            'breadcrumb_data' => $sub_order->sn,
        ]);
    }

    public function storeInbound(Request $request, $id)
    {
        $request->validate([
            'depot_id' => 'required|numeric',
            'event_item_id.*' => 'required|numeric',
            'product_style_id.*' => 'required|numeric',
            'inbound_date.*' => 'required|string',
            'inbound_num.*' => 'required|numeric',
            'error_num.*' => 'required|numeric|min:0',
            'status.*' => 'required|numeric|min:0',
            'expiry_date.*' => 'required|string',
            'prd_type.*' => 'required|string',
        ]);
        $depot_id = $request->input('depot_id');
        $inboundItemReq = $request->only('event_item_id', 'product_style_id', 'inbound_date', 'inbound_num', 'error_num', 'inbound_memo', 'status', 'expiry_date', 'inbound_memo', 'prd_type');

        if (isset($inboundItemReq['product_style_id'])) {
            //檢查若輸入實進數量小於0，打負數時備註欄位要必填說明原因
            foreach ($inboundItemReq['product_style_id'] as $key => $val) {
                if (1 > $inboundItemReq['inbound_num'][$key] && true == empty($inboundItemReq['inbound_memo'][$key])) {
                    throw ValidationException::withMessages(['inbound_memo.'.$key => '打負數時備註欄位要必填說明原因']);
                }
            }

            $depot = Depot::where('id', '=', $depot_id)->get()->first();
            $style_arr = PurchaseInbound::getCreateData(Event::ord_pickup()->value, $id, $inboundItemReq['event_item_id'], $inboundItemReq['product_style_id']);

            $result = DB::transaction(function () use ($inboundItemReq, $id, $depot_id, $depot, $request, $style_arr
            ) {
                foreach ($style_arr as $key => $val) {
                    $re = PurchaseInbound::createInbound(
                        Event::ord_pickup()->value,
                        $id,
                        $inboundItemReq['event_item_id'][$key], //存入 dlv_receive_depot.id
                        $inboundItemReq['product_style_id'][$key],
                        $val['item']['title'] . '-'. $val['item']['spec'],
                        $val['unit_cost'],
                        $inboundItemReq['expiry_date'][$key],
                        $inboundItemReq['inbound_date'][$key],
                        $inboundItemReq['inbound_num'][$key],
                        $depot_id,
                        $depot->name,
                        $request->user()->id,
                        $request->user()->name,
                        $inboundItemReq['inbound_memo'][$key],
                        $inboundItemReq['prd_type'][$key],
                    );
                    if ($re['success'] == 0) {
                        DB::rollBack();
                        return $re;
                    }
                }
                return ['success' => 1, 'error_msg' => ""];
            });
            if ($result['success'] == 0) {
                wToast($result['error_msg']);
            } else {
                wToast(__('Add finished.'));
            }
        }
        return redirect(Route('cms.order.inbound', [
            'subOrderId' => $id,
        ]));
    }

    public function deleteInbound(Request $request, $id)
    {
        $inboundData = PurchaseInbound::where('id', '=', $id);
        $inboundDataGet = $inboundData->get()->first();
        $purchase_id = '';
        if (null != $inboundDataGet) {
            $purchase_id = $inboundDataGet->event_id;
        } else {
            return abort(404);
        }
        $re = PurchaseInbound::delInbound($id, $request->user()->id);
        if ($re['success'] == 0) {
            wToast($re['error_msg']);
        } else {
            wToast(__('Delete finished.'));
        }
        return redirect(Route('cms.order.inbound', [
            'subOrderId' => $purchase_id,
        ]));
    }


    public function pay_order(Request $request, $id, $sid)
    {
        $request->merge([
            'id'=>$id,
            'sid'=>$sid,
        ]);

        $request->validate([
            'id' => 'required|exists:ord_orders,id',
            'sid' => 'required|exists:ord_sub_orders,id',
        ]);

        $source_type = app(Order::class)->getTable();
        $type = 1;

        $paying_order = PayingOrder::where([
            'source_type'=>$source_type,
            'source_id'=>$id,
            'source_sub_id'=>$sid,
            'type'=>$type,
            'deleted_at'=>null,
        ])->first();

        if($request->isMethod('post')){
            if(! $paying_order){
                $price = Order::subOrderDetail($id, $sid, true)->get()->toArray()[0]->logistic_cost;
                $product_grade = PayableDefault::where('name', '=', 'product')->first()->default_grade_id;
                $logistics_grade = PayableDefault::where('name', '=', 'logistics')->first()->default_grade_id;

                PayingOrder::createPayingOrder(
                    $source_type,
                    $id,
                    $sid,
                    $request->user()->id,
                    $type,
                    $product_grade,
                    $logistics_grade,
                    $price ?? 0,
                    '',
                    '',
                );
            }

            return redirect(Route('cms.order.pay-order', [
                'id' => $id,
                'sid' => $sid,
            ]));

        } else {

            if(! $paying_order) {
                return abort(404);
            }

            $order = Order::orderDetail($id)->get()->first();
            $sub_order = Order::subOrderDetail($id, $sid, true)->get()->toArray()[0];

            $supplier = Supplier::find($sub_order->supplier_id);
            $undertaker = User::find($paying_order->usr_users_id);
            $applied_company = DB::table('acc_company')->where('id', 1)->first();

            $logistics_grade = AllGrade::find($paying_order->logistics_grade_id)->eachGrade->code . ' - ' . AllGrade::find($paying_order->logistics_grade_id)->eachGrade->name;

            $pay_off = false;
            $pay_off_date = date('Y-m-d', strtotime($paying_order->created_at));
            $accountant = null;

            if($paying_order->balance_date){
                $pay_off = true;
                $pay_off_date = date('Y-m-d', strtotime($paying_order->balance_date));
                $pay_record = AccountPayable::where('pay_order_id', $paying_order->id);
                $accountant = User::find($pay_record->latest()->first()->accountant_id_fk);
            }

            return view('cms.commodity.order.pay_order', [
                'breadcrumb_data' => ['id' => $id, 'sn' => $order->sn],

                'paying_order' => $paying_order,
                'order' => $order,
                'sub_order' => $sub_order,
                'supplier' => $supplier,
                'undertaker' => $undertaker,
                'applied_company' => $applied_company,
                'logistics_grade' => $logistics_grade,
                'pay_off' => $pay_off,
                'pay_off_date' => $pay_off_date,
                'accountant' => $accountant,
            ]);
        }
    }
}
