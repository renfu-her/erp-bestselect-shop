<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Area\Area;
use App\Enums\Customer\ProfitStatus;
use App\Enums\Delivery\Event;
use App\Enums\Delivery\LogisticStatus;
use App\Enums\Discount\DividendCategory;
use App\Enums\Order\OrderStatus;
use App\Enums\Order\UserAddrType;
use App\Enums\Payable\ChequeStatus as PayableChequeStatus;
use App\Enums\Received\ChequeStatus;
use App\Enums\Received\ReceivedMethod;
use App\Enums\Supplier\Payment;
use App\Http\Controllers\Controller;
use App\Models\AccountPayable;
use App\Models\Addr;
use App\Models\AllGrade;
use App\Models\CrdCreditCard;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerDividend;
use App\Models\CustomerProfit;
use App\Models\DayEnd;
use App\Models\Delivery;
use App\Models\Depot;
use App\Models\Discount;
use App\Models\GeneralLedger;
use App\Models\LogisticFlow;
use App\Models\Order;
use App\Models\OrderCart;
use App\Models\OrderFlow;
use App\Models\OrderInvoice;
use App\Models\OrderItem;
use App\Models\OrderPayCreditCard;
use App\Models\OrderProfit;
use App\Models\OrderProfitLog;
use App\Models\OrderRemit;
use App\Models\PayableAccount;
use App\Models\PayableCash;
use App\Models\PayableCheque;
use App\Models\PayableDefault;
use App\Models\PayableForeignCurrency;
use App\Models\PayableOther;
use App\Models\PayableRemit;
use App\Models\PayingOrder;
use App\Models\Product;
use App\Models\PurchaseInbound;
use App\Models\ReceivedDefault;
use App\Models\ReceiveDepot;
use App\Models\ReceivedOrder;
use App\Models\SaleChannel;
use App\Models\ShipmentCategory;
use App\Models\ShipmentGroup;
use App\Models\SubOrders;
use App\Models\Supplier;
use App\Models\User;
use App\Models\UserSalechannel;
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
        $page = getPageCount(Arr::get($query, 'data_per_page'));
        $cond['keyword'] = Arr::get($query, 'keyword', null);
        $cond['order_status'] = Arr::get($query, 'order_status', []);
        $cond['shipment_status'] = Arr::get($query, 'shipment_status', []);
        $cond['sale_channel_id'] = Arr::get($query, 'sale_channel_id', []);
        $cond['order_sdate'] = Arr::get($query, 'order_sdate', null);
        $cond['order_edate'] = Arr::get($query, 'order_edate', null);
        $cond['profit_user'] = Arr::get($query, 'profit_user', null);
        $cond['item_title'] = Arr::get($query, 'item_title', null);
        $cond['purchase_sn'] = Arr::get($query, 'purchase_sn', null);

        $order_date = null;
        if ($cond['order_sdate'] && $cond['order_edate']) {
            $order_date = [$cond['order_sdate'], $cond['order_edate']];
        }

        if (gettype($cond['shipment_status']) == 'string') {
            $cond['shipment_status'] = explode(',', $cond['shipment_status']);
        } else {
            $cond['shipment_status'] = [];
        }

        $dataList = Order::orderList($cond['keyword'],
            $cond['order_status'],
            $cond['sale_channel_id'],
            $order_date,
            $cond['shipment_status'],
            $cond['profit_user'],
            null,
            $cond['item_title'],
            $cond['purchase_sn'])
            ->paginate($page)->appends($query);

        $orderStatus = [];
        foreach (\App\Enums\Order\OrderStatus::asArray() as $key => $val) {
            $orderStatus[$val] = \App\Enums\Order\OrderStatus::getDescription($val);
        }

        $profitUsers = CustomerProfit::dataList(null, null, 'success')->get();

        return view('cms.commodity.order.list', [
            'dataList' => $dataList,
            'cond' => $cond,
            'orderStatus' => $orderStatus,
            'shipmentStatus' => LogisticStatus::asArray(),
            'saleChannels' => SaleChannel::select('id', 'title')->get()->toArray(),
            'data_per_page' => $page,
            'profitUsers' => $profitUsers]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // Order::assign_dividend_active_date(35);
        //   dd(Discount::checkCode('fkfk',[1,2,4]));

        $query = $request->query();
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

        $customer = $request->user();
        $customer_id = $customer->customer_id;
        $customer_email = '';
        $mcode = '';
        if ($customer_id) {
            $salechannels = UserSalechannel::getSalechannels($request->user()->id)->get()->toArray();
            if (CustomerProfit::getProfitData($customer_id, ProfitStatus::Success())) {
                $mcode = Customer::where('id', $customer_id)->get()->first()->sn;
            }
            $customer_in_db = Customer::where('id', $customer_id)->get()->first();
            $customer_email = ($customer_in_db) ? $customer_in_db->email : '';
        } else {
            $salechannels = [];
        }

        $citys = Addr::getCitys();
        $defaultAddress = DB::table('usr_customers')
            ->where('usr_customers.id', '=', $customer_id)
            ->leftJoin('usr_customers_address', 'usr_customers.id', '=', 'usr_customers_address.usr_customers_id_fk')
            ->where('is_default_addr', '=', 1)
            ->select([
                'usr_customers.id',
                'usr_customers_address.name',
                'usr_customers_address.phone',
                'address',
                'addr',
                'city_id',
                'region_id',
            ])
            ->get()->first();

        $otherOftenUsedAddresses = DB::table('usr_customers')
            ->where('usr_customers.id', '=', $customer_id)
            ->leftJoin('usr_customers_address', 'usr_customers.id', '=', 'usr_customers_address.usr_customers_id_fk')
            ->where('is_default_addr', '=', 0)
            ->select([
                'usr_customers.id',
                'usr_customers_address.name',
                'usr_customers_address.phone',
                'usr_customers_address.id as customer_addr_id',
                'is_default_addr',
                'address',
                'addr',
                'city_id',
                'region_id',
            ])
            ->get();

        //    dd(Discount::getDiscounts('global-normal'));

        return view('cms.commodity.order.edit', [
            'customer_id' => $customer_id,
            'customer_email' => $customer_email,
            'customers' => Customer::where('id', $customer_id)->get(),
            'defaultAddress' => $defaultAddress,
            'otherOftenUsedAddresses' => $otherOftenUsedAddresses,
            'citys' => $citys,
            'cart' => $cart,
            'regions' => $regions,
            'overbought_id' => $overbought_id,
            'salechannels' => $salechannels,
            'discounts' => Discount::getDiscounts('global-normal'),
            'query' => $query,
            'mcode' => $mcode,
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
        $query = $request->query();
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
            $arrVali[$prefix . '_radio'] = 'required';
            $arrVali[$prefix . '_address'] = 'required';

        }

        $request->validate(array_merge([
            'customer_id' => 'required',
            'product_id' => 'required|array',
            'product_style_id' => 'required|array',
            'shipment_type' => 'required|array',
            'shipment_event_id' => 'required|array',
            'salechannel_id' => 'required',

            'invoice_method' => 'required|in:print,give,e_inv',
            'love_code' => 'required_if:invoice_method,==,give',
            'carrier_type' => 'required_if:invoice_method,==,e_inv|in:0,1,2',
            'carrier_num' => 'required_if:carrier_type,==,0|required_if:carrier_type,==,1',
            'carrier_email' => 'required_if:carrier_type,==,2',
        ], $arrVali));

        $d = $request->all();

        $dividend = [];
        foreach ($d['dividend_id'] as $key => $div) {
            $dividend[$div] = $d['dividend'][$key];
        }
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

        $payinfo = null;
        $payinfo['category'] = $d['category'] ?? null;
        $payinfo['invoice_method'] = $d['invoice_method'] ?? null;
        $payinfo['inv_title'] = $d['inv_title'] ?? null;
        $payinfo['buyer_ubn'] = $d['buyer_ubn'] ?? null;
        $payinfo['love_code'] = $d['love_code'] ?? null;
        $payinfo['carrier_type'] = $d['carrier_type'] ?? null;
        $payinfo['carrier_email'] = $d['carrier_email'] ?? null;
        $payinfo['carrier_num'] = $d['carrier_num'] ?? null;

        $re = Order::createOrder($customer->email, $d['salechannel_id'], $address, $items, $d['mcode'] ?? null, $d['note'], $coupon, $payinfo, null, $dividend, $request->user());

        if ($re['success'] == '1') {
            CustomerAddress::addCustomerAddress($d, $customer->id);
            wToast('訂單新增成功');
            return redirect(route('cms.order.detail', [
                'id' => $re['order_id'],
            ]));
        }
        if (isset($query['debug'])) {
            dd($re);
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
                case "dividend":
                    $errors['dividend'] = $re['error_msg'];
                    break;
            }
        }
        if (isset($re['error_msg']) && '0' == $re['success']) {
            $errors['error_msg'] = $re['error_msg'];
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
        list($order, $subOrder) = $this->getOrderAndSubOrders($id, $subOrderId);

        if (!$order) {
            return abort(404);
        }
        $remit = OrderRemit::getData($order->id)->get()->first();

        $delivery = null;
        if (isset($subOrderId)) {
            $delivery = Delivery::where('event', Event::order()->value)->where('event_id', $subOrderId)->first();
        }

        $sn = $order->sn;

        $receivable = false;
        $source_type = app(Order::class)->getTable();
        $received_order_collection = ReceivedOrder::where([
            'source_type' => $source_type,
            'source_id' => $id,
        ]);
        $received_order = $received_order_collection->first();
        if ($received_order && $received_order->balance_date) {
            $receivable = true;
        }
        $received_credit_card_log = OrderPayCreditCard::where([
            'source_type' => $source_type,
            'source_id' => $id,
            'status' => 0,
            'authamt' => $order->total_price,
            'lidm' => $sn,
        ])->orderBy('created_at', 'DESC')->first();

        $dividend = CustomerDividend::where('category', DividendCategory::Order())
            ->where('category_sn', $order->sn)
            ->where('type', 'get')->get()->first();

        if ($dividend) {
            $dividend = $dividend->dividend;
        } else {
            $dividend = 0;
        }

        return view('cms.commodity.order.detail', [
            'sn' => $sn,
            'order' => $order,
            'subOrders' => $subOrder,
            'remit' => $remit,
            'breadcrumb_data' => $sn,
            'subOrderId' => $subOrderId,
            'discounts' => Discount::orderDiscountList('main', $id)->get()->toArray(),
            'receivable' => $receivable,
            'received_order' => $received_order,
            'received_credit_card_log' => $received_credit_card_log,
            'dividend' => $dividend,
            'canCancel' => Order::checkCanCancel($id, 'backend'),
            'delivery' => $delivery,
            'canSplit' => Order::checkCanSplit($id),
            'po_check' => $delivery ? PayingOrder::source_confirmation(app(Delivery::class)->getTable(), $delivery->id) : true,
        ]);
    }

    //取得訂單和子訂單(可選)
    public function getOrderAndSubOrders(int $id, int $subOrderId = null): array
    {
        $order = Order::orderDetail($id)->get()->first();
        $subOrder = Order::subOrderDetail($id, $subOrderId, true)->get()->toArray();

        foreach ($subOrder as $key => $value) {
            $subOrder[$key]->items = json_decode($value->items);
            $subOrder[$key]->consume_items = json_decode($value->consume_items);
        }
        return array($order, $subOrder);
    }

    // 列印－銷貨單明細
    public function print_order_sales(Request $request, $id, $subOrderId)
    {
        list($order, $subOrder) = $this->getOrderAndSubOrders($id, $subOrderId);

        if (!$order) {
            return abort(404);
        }
        if ($subOrder && 0 < count($subOrder)) {
            $subOrder = $subOrder[0];
        }
        return view('doc.print_order', [
            'type' => 'sales',
            'user' => $request->user(),
            'order' => $order,
            'subOrders' => $subOrder,
        ]);
    }

    // 列印－出貨單明細
    public function print_order_ship(Request $request, $id, $subOrderId)
    {
        list($order, $subOrder) = $this->getOrderAndSubOrders($id, $subOrderId);

        if (!$order) {
            return abort(404);
        }
        if ($subOrder && 0 < count($subOrder)) {
            $subOrder = $subOrder[0];
        }
        return view('doc.print_order', [
            'type' => 'ship',
            'user' => $request->user(),
            'order' => $order,
            'subOrders' => $subOrder,
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

    public function inbound(Request $request, $subOrderId)
    {
        $sub_order = DB::table('ord_sub_orders as sub_order')
            ->leftJoin('dlv_delivery as delivery', function ($join) {
                $join->on('delivery.event_id', '=', 'sub_order.id')
                    ->where('delivery.event', '=', Event::order()->value);
            })
            ->leftJoin('prd_pickup as pick_up', function ($join) {
                $join->on('pick_up.id', '=', 'sub_order.ship_event_id')
                    ->where('sub_order.ship_category', '=', 'pickup');
            })
            ->select(
                'sub_order.id as id'
                , 'sub_order.order_id as order_id'
                , 'sub_order.sn as sn'
                , 'sub_order.ship_category as ship_category'
                , 'delivery.audit_date'
                , 'pick_up.depot_id_fk as depot_id'
            )
            ->where('sub_order.id', '=', $subOrderId)
            ->get()->first();

        if (!$sub_order || 'pickup' != $sub_order->ship_category) {
            return abort(404);
        }
        $purchaseItemList = ReceiveDepot::getShouldEnterNumDataList(Event::order()->value, $subOrderId);

        $inboundList = PurchaseInbound::getInboundList(['event' => Event::ord_pickup()->value, 'purchase_id' => $subOrderId])
            ->orderByDesc('inbound.created_at')
            ->get()->toArray();
        $inboundOverviewList = PurchaseInbound::getOverviewInboundList(Event::ord_pickup()->value, $subOrderId)->get()->toArray();

        $depotList = Depot::all()->toArray();
        return view('cms.commodity.order.inbound', [
            'purchaseData' => $sub_order,
            'send_depot_id' => $sub_order->depot_id,
            'purchaseItemList' => $purchaseItemList->get(),
            'inboundList' => $inboundList,
            'inboundOverviewList' => $inboundOverviewList,
            'depotList' => $depotList,
            'formAction' => Route('cms.order.store_inbound', ['id' => $subOrderId]),
            //'formActionClose' => Route('cms.order.close', ['id' => $subOrderId,]),
            'breadcrumb_data' => ['id' => $sub_order->id, 'sn' => $sub_order->sn],
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
                    throw ValidationException::withMessages(['inbound_memo.' . $key => '打負數時備註欄位要必填說明原因']);
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
                        $val['item']['title'] . '-' . $val['item']['spec'],
                        $val['sku'],
                        $val['unit_cost'],
                        $inboundItemReq['expiry_date'][$key] ?? null,
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

    public function ro_edit(Request $reqeust, $id)
    {
        $order_id = request('id');
        $order_data = Order::findOrFail($order_id);

        $order_purchaser = Customer::leftJoin('usr_customers_address AS customer_add', function ($join) {
            $join->on('usr_customers.id', '=', 'customer_add.usr_customers_id_fk');
            $join->where([
                'customer_add.is_default_addr' => 1,
            ]);
        })->where([
            'email' => $order_data->email,
            // 'deleted_at'=>null,
        ])->select(
            'usr_customers.id',
            'usr_customers.name',
            'usr_customers.phone AS phone',
            'usr_customers.email',
            'customer_add.address AS address'
        )->first();

        $order_list_data = OrderItem::item_order($order_id)->get();

        $source_type = app(Order::class)->getTable();
        $received_order_collection = ReceivedOrder::where([
            'source_type' => $source_type,
            'source_id' => $order_id,
        ]);

        if (!$received_order_collection->first()) {
            ReceivedOrder::create_received_order($source_type, $order_id);
        }

        $received_order_data = $received_order_collection->get();
        $received_data = ReceivedOrder::get_received_detail($received_order_data->pluck('id')->toArray());

        $tw_price = $received_order_data->sum('price') - $received_data->sum('tw_price');
        if ($tw_price == 0) {
            // dd('此付款單金額已收齊');
        }

        $defaultData = [];
        foreach (ReceivedMethod::asArray() as $receivedMethod) {
            $defaultData[$receivedMethod] = DB::table('acc_received_default')
                ->where('name', '=', $receivedMethod)
                ->doesntExistOr(function () use ($receivedMethod) {
                    return DB::table('acc_received_default')
                        ->where('name', '=', $receivedMethod)
                        ->select('default_grade_id')
                        ->get();
                });
        }

        $total_grades = GeneralLedger::total_grade_list();

        $allGradeArray = [];
        // $allGrade = AllGrade::all();
        // $gradeModelArray = GradeModelClass::asSelectArray();

        // foreach ($allGrade as $grade) {
        //     $allGradeArray[$grade->id] = [
        //         'grade_id' => $grade->id,
        //         'grade_num' => array_keys($gradeModelArray, $grade->grade_type)[0],
        //         'code' => $grade->eachGrade->code,
        //         'name' => $grade->eachGrade->name,
        //     ];
        // }

        foreach ($total_grades as $grade) {
            $allGradeArray[$grade['primary_id']] = $grade;
        }
        $defaultArray = [];
        foreach ($defaultData as $recMethod => $ids) {
            // 收款方式若沒有預設、或是方式為「其它」，則自動帶入所有會計科目
            if ($ids !== true &&
                $recMethod !== 'other') {
                foreach ($ids as $id) {
                    $defaultArray[$recMethod][$id->default_grade_id] = [
                        // 'methodName' => $recMethod,
                        'method' => ReceivedMethod::getDescription($recMethod),
                        'grade_id' => $id->default_grade_id,
                        'grade_num' => $allGradeArray[$id->default_grade_id]['grade_num'],
                        'code' => $allGradeArray[$id->default_grade_id]['code'],
                        'name' => $allGradeArray[$id->default_grade_id]['name'],
                    ];
                }
            } else {
                if ($recMethod == 'other') {
                    $defaultArray[$recMethod] = $allGradeArray;
                } else {
                    $defaultArray[$recMethod] = [];
                }
            }
        }

        $currencyDefault = DB::table('acc_currency')
            ->leftJoin('acc_received_default', 'acc_currency.received_default_fk', '=', 'acc_received_default.id')
            ->select(
                'acc_currency.name as currency_name',
                'acc_currency.id as currency_id',
                'acc_currency.rate',
                'default_grade_id',
                'acc_received_default.name as method_name'
            )
            ->orderBy('acc_currency.id')
            ->get();
        $currencyDefaultArray = [];
        foreach ($currencyDefault as $default) {
            $currencyDefaultArray[$default->default_grade_id][] = [
                'currency_id' => $default->currency_id,
                'currency_name' => $default->currency_name,
                'rate' => $default->rate,
                'default_grade_id' => $default->default_grade_id,
            ];
        }

        $order_discount = DB::table('ord_discounts')->where([
            'order_type' => 'main',
            'order_id' => $order_id,
        ])->where('discount_value', '>', 0)->get()->toArray();

        foreach ($order_discount as $value) {
            $value->account_code = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->code : '4000';
            $value->account_name = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->name : '無設定會計科目';
        }

        $card_type = CrdCreditCard::distinct('title')->groupBy('title')->orderBy('id', 'asc')->pluck('title', 'id')->toArray();

        $checkout_area = Area::get_key_value();

        return view('cms.commodity.order.ro_edit', [
            'defaultArray' => $defaultArray,
            'currencyDefaultArray' => $currencyDefaultArray,
            'tw_price' => $tw_price,
            'receivedMethods' => ReceivedMethod::asSelectArray(),
            'formAction' => Route('cms.order.ro-store', ['id' => $order_id]),
            'ord_orders_id' => $order_id,

            'breadcrumb_data' => ['id' => $order_data->id, 'sn' => $order_data->sn],
            'order_data' => $order_data,
            'order_purchaser' => $order_purchaser,
            'order_list_data' => $order_list_data,
            'order_discount' => $order_discount,
            'received_order_data' => $received_order_data,
            'received_data' => $received_data,
            'card_type' => $card_type,
            'checkout_area' => $checkout_area,
        ]);
    }

    public function ro_store(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:' . app(Order::class)->getTable() . ',id',
            'acc_transact_type_fk' => 'required|string|in:' . implode(',', ReceivedMethod::asArray()),
            'tw_price' => 'required|numeric',
            request('acc_transact_type_fk') => 'required|array',
            request('acc_transact_type_fk') . '.grade' => 'required|exists:acc_all_grades,id',
            'summary' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $data = $request->except('_token');
        $received_order_collection = ReceivedOrder::where([
            'source_type' => app(Order::class)->getTable(),
            'source_id' => $data['id'],
        ]);
        $received_order_id = $received_order_collection->first()->id;

        DB::beginTransaction();

        try {
            // 'credit_card'
            if ($data['acc_transact_type_fk'] == ReceivedMethod::CreditCard) {
                $card_type = CrdCreditCard::distinct('title')->groupBy('title')->orderBy('id', 'asc')->pluck('title', 'id')->toArray();

                $checkout_area = Area::get_key_value();

                $data[$data['acc_transact_type_fk']] = [
                    'cardnumber' => $data[$data['acc_transact_type_fk']]['cardnumber'],
                    'authamt' => $data['tw_price'] ?? 0,
                    'checkout_date' => $data[$data['acc_transact_type_fk']]['checkout_date'] ?? null, // date('Y-m-d H:i:s')
                    'card_type_code' => $data[$data['acc_transact_type_fk']]['card_type_code'] ?? null,
                    'card_type' => $card_type[$data[$data['acc_transact_type_fk']]['card_type_code']] ?? null,
                    'card_owner_name' => $data[$data['acc_transact_type_fk']]['card_owner_name'] ?? null,
                    'authcode' => $data[$data['acc_transact_type_fk']]['authcode'] ?? null,
                    'all_grades_id' => $data[$data['acc_transact_type_fk']]['grade'],
                    'checkout_area_code' => 'taipei', // $data[$data['acc_transact_type_fk']]['credit_card_area_code']
                    'checkout_area' => '台北', // $checkout_area[$data[$data['acc_transact_type_fk']]['credit_card_area_code']]
                    'installment' => $data[$data['acc_transact_type_fk']]['installment'] ?? 'none',
                    'status_code' => 0,
                    'card_nat' => 'local',
                    'checkout_mode' => 'offline',
                ];

                $data[$data['acc_transact_type_fk']]['grade'] = $data[$data['acc_transact_type_fk']]['all_grades_id'];

                $EncArray['more_info'] = $data[$data['acc_transact_type_fk']];

            } else if ($data['acc_transact_type_fk'] == ReceivedMethod::Cheque) {
                $request->validate([
                    request('acc_transact_type_fk') . '.ticket_number' => 'required|unique:acc_received_cheque,ticket_number,ro_delete,status_code|regex:/^[A-Z]{2}[0-9]{7}$/',
                ]);
            }

            $result_id = ReceivedOrder::store_received_method($data);

            $parm = [];
            $parm['received_order_id'] = $received_order_id;
            $parm['received_method'] = $data['acc_transact_type_fk'];
            $parm['received_method_id'] = $result_id;
            $parm['grade_id'] = $data[$data['acc_transact_type_fk']]['grade'];
            $parm['price'] = $data['tw_price'];
            // $parm['accountant_id_fk'] = auth('user')->user()->id;
            $parm['summary'] = $data['summary'];
            $parm['note'] = $data['note'];
            ReceivedOrder::store_received($parm);

            if ($data['acc_transact_type_fk'] == ReceivedMethod::CreditCard) {
                OrderPayCreditCard::create_log(app(Order::class)->getTable(), $data['id'], (object) $EncArray);
            }

            DB::commit();
            wToast(__('收款單儲存成功'));

        } catch (\Exception $e) {
            DB::rollback();
            wToast(__('收款單儲存失敗', ['type' => 'danger']));
        }

        if (ReceivedOrder::find($received_order_id) && ReceivedOrder::find($received_order_id)->balance_date) {
            return redirect()->route('cms.order.detail', [
                'id' => $data['id'],
            ]);

        } else {
            return redirect()->route('cms.order.ro-edit', [
                'id' => $data['id'],
            ]);
        }
    }

    public function ro_receipt(Request $request, $id)
    {
        $request->merge([
            'id' => $id,
        ]);
        $request->validate([
            'id' => 'required|exists:ord_orders,id',
        ]);

        $order = Order::findOrFail(request('id'));
        $received_order_collection = ReceivedOrder::where([
            'source_type' => app(Order::class)->getTable(),
            'source_id' => $id,
        ]);

        $received_order_data = $received_order_collection->get();
        if (count($received_order_data) == 0 || !$received_order_collection->first()->balance_date) {
            return abort(404);
        }

        $order_list_data = OrderItem::item_order(request('id'))->get();
        $product_qc = $order_list_data->pluck('product_user_name')->toArray();
        $product_qc = array_unique($product_qc);
        asort($product_qc);

        $received_data = ReceivedOrder::get_received_detail($received_order_data->pluck('id')->toArray());
        $data_status_check = ReceivedOrder::received_data_status_check($received_data);

        $undertaker = User::find($received_order_collection->first()->usr_users_id);

        // $accountant = User::whereIn('id', $received_data->pluck('accountant_id_fk')->toArray())->get();
        // $accountant = array_unique($accountant->pluck('name')->toArray());
        // asort($accountant);
        $accountant = User::find($received_order_collection->first()->accountant_id) ? User::find($received_order_collection->first()->accountant_id)->name : null;

        $product_grade_name = AllGrade::find($received_order_collection->first()->product_grade_id)->eachGrade->code . ' ' . AllGrade::find($received_order_collection->first()->product_grade_id)->eachGrade->name;

        $logistics_grade = AllGrade::find($received_order_collection->first()->logistics_grade_id);
        if (isset($logistics_grade)) {
            $logistics_grade_name = $logistics_grade->eachGrade->code . ' ' . $logistics_grade->eachGrade->name;
        }

        $order_discount = DB::table('ord_discounts')->where([
            'order_type' => 'main',
            'order_id' => request('id'),
        ])->where('discount_value', '>', 0)->get()->toArray();

        foreach ($order_discount as $value) {
            $value->account_code = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->code : '4000';
            $value->account_name = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->name : '無設定會計科目';
        }

        $zh_price = num_to_str($received_order_collection->first()->price);

        $view = 'cms.commodity.order.ro_receipt';
        if (request('action') == 'print') {
            // 列印－收款單
            $view = 'doc.print_received';
        }
        return view($view, [
            'breadcrumb_data' => ['id' => $order->id, 'sn' => $order->sn],

            'received_order' => $received_order_collection->first(),
            'order' => $order,
            'order_discount' => $order_discount,
            'order_list_data' => $order_list_data,
            'received_data' => $received_data,
            'data_status_check' => $data_status_check,
            'undertaker' => $undertaker,
            'product_qc' => implode(',', $product_qc),
            // 'accountant'=>implode(',', $accountant),
            'accountant' => $accountant,
            'product_grade_name' => $product_grade_name,
            'logistics_grade_name' => $logistics_grade_name ?? '',
            'zh_price' => $zh_price,
        ]);
    }

    public function ro_review(Request $request, $id)
    {
        $request->merge([
            'id' => $id,
        ]);
        $request->validate([
            'id' => 'required|exists:ord_orders,id',
        ]);

        $received_order_collection = ReceivedOrder::where([
            'source_type' => app(Order::class)->getTable(),
            'source_id' => $id,
        ]);

        $received_order_data = $received_order_collection->get();
        if (count($received_order_data) == 0 || !$received_order_collection->first()->balance_date) {
            return abort(404);
        }

        $received_order = $received_order_collection->first();

        if ($request->isMethod('post')) {
            $request->validate([
                'receipt_date' => 'required|date_format:"Y-m-d"',
                'invoice_number' => 'nullable|string',
            ]);

            DB::beginTransaction();

            try {
                $received_order->update([
                    'accountant_id' => auth('user')->user()->id,
                    'receipt_date' => request('receipt_date'),
                    'invoice_number' => request('invoice_number'),
                ]);

                if (is_array(request('received_method'))) {
                    $unique_m = array_unique(request('received_method'));

                    foreach ($unique_m as $m_value) {
                        if (in_array($m_value, ReceivedMethod::asArray()) && is_array(request($m_value))) {
                            $req = request($m_value);
                            foreach ($req as $r) {
                                $r['received_method'] = $m_value;
                                ReceivedOrder::update_received_method($r);
                            }
                        }
                    }
                }

                OrderFlow::changeOrderStatus($id, OrderStatus::Received());
                // 配發啟用日期
                Order::assign_dividend_active_date($id);
                Order::sendMail_OrderPaid($id);
                Customer::updateOrderSpends($received_order->drawee_id, $received_order->price);

                //修改子訂單物流配送狀態為檢貨中
                $sub_orders = SubOrders::where('order_id', '=', $id)->get();
                if (isset($sub_orders) && 0 < count($sub_orders)) {
                    $sub_order_ids = [];
                    foreach ($sub_orders as $sub_order) {
                        array_push($sub_order_ids, $sub_order->id);
                    }
                    $delivery = Delivery::whereIn('event_id', $sub_order_ids)->where('event', '=', Event::order()->value)->get();
                    if (isset($delivery) && 0 < count($delivery)) {
                        foreach ($delivery as $dlv) {
                            $reLFCDS = LogisticFlow::createDeliveryStatus($request->user(), $dlv->id, [LogisticStatus::A2000()]);
                            if ($reLFCDS['success'] == 0) {
                                DB::rollBack();
                                return $reLFCDS;
                            }
                        }
                    }
                }

                DayEnd::match_day_end_status(request('receipt_date'), $received_order->sn);

                DB::commit();
                wToast(__('入帳日期更新成功'));

                return redirect()->route('cms.order.ro-receipt', ['id' => request('id')]);

            } catch (\Exception $e) {
                DB::rollback();
                wToast(__('入帳日期更新失敗', ['type' => 'danger']));

                return redirect()->back();
            }

        } else if ($request->isMethod('get')) {
            $received_data = ReceivedOrder::get_received_detail($received_order_data->pluck('id')->toArray());
            $data_status_check = ReceivedOrder::received_data_status_check($received_data);

            if ($received_order->receipt_date) {
                if ($data_status_check) {
                    return redirect()->back();
                }

                DayEnd::match_day_end_status($received_order->receipt_date, $received_order->sn);

                $received_order->update([
                    'accountant_id' => null,
                    'receipt_date' => null,
                ]);

                OrderFlow::changeOrderStatus($id, OrderStatus::Paided());
                Customer::updateOrderSpends($received_order->drawee_id, $received_order->price * -1);

                wToast(__('入帳日期已取消'));
                return redirect()->route('cms.order.ro-receipt', ['id' => request('id')]);

            } else {
                $undertaker = User::find($received_order->usr_users_id);
                $order = Order::findOrFail(request('id'));

                $order_list_data = OrderItem::item_order(request('id'))->get();

                $debit = [];
                $credit = [];

                // 收款項目
                foreach ($received_data as $value) {
                    $name = $value->received_method_name . ' ' . $value->summary . '（' . $value->account->code . ' - ' . $value->account->name . '）';
                    // GeneralLedger::classification_processing($debit, $credit, $value->master_account->code, $name, $value->tw_price, 'r', 'received');

                    $tmp = [
                        'account_code' => $value->account->code,
                        'name' => $name,
                        'price' => $value->tw_price,
                        'type' => 'r',
                        'd_type' => 'received',

                        'account_name' => $value->account->name,
                        'method_name' => $value->received_method_name,
                        'summary' => $value->summary,
                        'note' => $value->note,
                        'product_title' => null,
                        'del_even' => null,
                        'del_category_name' => null,
                        'product_price' => null,
                        'product_qty' => null,
                        'product_owner' => null,
                        'discount_title' => null,
                        'payable_type' => null,

                        'received_info' => $value,
                    ];
                    GeneralLedger::classification_processing($debit, $credit, $tmp);
                }

                // 商品
                $product_account = AllGrade::find($received_order->product_grade_id) ? AllGrade::find($received_order->product_grade_id)->eachGrade : null;
                $account_code = $product_account ? $product_account->code : '4000';
                $account_name = $product_account ? $product_account->name : '無設定會計科目';
                $product_grade_name = $account_code . ' ' . $account_name;
                foreach ($order_list_data as $value) {
                    $name = $product_grade_name . ' --- ' . $value->product_title . '（' . $value->del_even . ' - ' . $value->del_category_name . '）（' . $value->product_price . ' * ' . $value->product_qty . '）';
                    // GeneralLedger::classification_processing($debit, $credit, $product_master_account->code, $name, $value->product_origin_price, 'r', 'product');

                    $tmp = [
                        'account_code' => $account_code,
                        'name' => $name,
                        'price' => $value->product_origin_price,
                        'type' => 'r',
                        'd_type' => 'product',

                        'account_name' => $account_name,
                        'method_name' => null,
                        'summary' => $value->summary ?? null,
                        'note' => $value->note ?? null,
                        'product_title' => $value->product_title,
                        'del_even' => $value->del_even ?? null,
                        'del_category_name' => $value->del_category_name ?? null,
                        'product_price' => $value->product_price,
                        'product_qty' => $value->product_qty,
                        'product_owner' => null,
                        'discount_title' => null,
                        'payable_type' => null,
                        'received_info' => null,
                    ];
                    GeneralLedger::classification_processing($debit, $credit, $tmp);
                }

                // 物流
                if ($order->dlv_fee != 0) {
                    $log_account = AllGrade::find($received_order->logistics_grade_id) ? AllGrade::find($received_order->logistics_grade_id)->eachGrade : null;
                    $account_code = $log_account ? $log_account->code : '4000';
                    $account_name = $log_account ? $log_account->name : '無設定會計科目';
                    // $name = $logistics_grade_name = $account_code . ' ' . $account_name;
                    $name = $account_code . ' ' . $account_name;
                    // GeneralLedger::classification_processing($debit, $credit, $logistics_master_account->code, $name, $order->dlv_fee, 'r', 'logistics');

                    $tmp = [
                        'account_code' => $account_code,
                        'name' => $name,
                        'price' => $order->dlv_fee,
                        'type' => 'r',
                        'd_type' => 'logistics',

                        'account_name' => $account_name,
                        'method_name' => null,
                        'summary' => null,
                        'note' => null,
                        'product_title' => null,
                        'del_even' => null,
                        'del_category_name' => null,
                        'product_price' => null,
                        'product_qty' => null,
                        'product_owner' => null,
                        'discount_title' => null,
                        'payable_type' => null,
                        'received_info' => null,
                    ];
                    GeneralLedger::classification_processing($debit, $credit, $tmp);
                }

                // 折扣
                if ($order->discount_value > 0) {
                    $order_discount = DB::table('ord_discounts')->where([
                        'order_type' => 'main',
                        'order_id' => request('id'),
                    ])->where('discount_value', '>', 0)->get()->toArray();

                    foreach ($order_discount as $value) {
                        $dis_account = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade : null;
                        $account_code = $dis_account ? $dis_account->code : '4000';
                        $account_name = $dis_account ? $dis_account->name : '無設定會計科目';
                        $name = $account_code . ' ' . $account_name . ' - ' . $value->title;
                        // GeneralLedger::classification_processing($debit, $credit, 4, $name, $order->discount_value, 'r', 'discount');

                        $tmp = [
                            'account_code' => $account_code,
                            'name' => $name,
                            'price' => $value->discount_value,
                            'type' => 'r',
                            'd_type' => 'discount',

                            'account_name' => $account_name,
                            'method_name' => null,
                            'summary' => null,
                            'note' => null,
                            'product_title' => null,
                            'del_even' => null,
                            'del_category_name' => null,
                            'product_price' => null,
                            'product_qty' => null,
                            'product_owner' => null,
                            'discount_title' => $value->title,
                            'payable_type' => null,
                            'received_info' => null,
                        ];
                        GeneralLedger::classification_processing($debit, $credit, $tmp);
                    }
                }

                $card_type = CrdCreditCard::distinct('title')->groupBy('title')->orderBy('id', 'asc')->pluck('title', 'id')->toArray();

                $checkout_area = Area::get_key_value();

                // grade process start
                $defaultData = [];
                foreach (ReceivedMethod::asArray() as $receivedMethod) {
                    $defaultData[$receivedMethod] = DB::table('acc_received_default')->where('name', '=', $receivedMethod)
                        ->doesntExistOr(function () use ($receivedMethod) {
                            return DB::table('acc_received_default')->where('name', '=', $receivedMethod)
                                ->select('default_grade_id')
                                ->get();
                        });
                }

                $total_grades = GeneralLedger::total_grade_list();
                $allGradeArray = [];

                foreach ($total_grades as $grade) {
                    $allGradeArray[$grade['primary_id']] = $grade;
                }
                $default_grade = [];
                foreach ($defaultData as $recMethod => $ids) {
                    if ($ids !== true &&
                        $recMethod !== 'other') {
                        foreach ($ids as $id) {
                            $default_grade[$recMethod][$id->default_grade_id] = [
                                // 'methodName' => $recMethod,
                                'method' => ReceivedMethod::getDescription($recMethod),
                                'grade_id' => $id->default_grade_id,
                                'grade_num' => $allGradeArray[$id->default_grade_id]['grade_num'],
                                'code' => $allGradeArray[$id->default_grade_id]['code'],
                                'name' => $allGradeArray[$id->default_grade_id]['name'],
                            ];
                        }
                    } else {
                        if ($recMethod == 'other') {
                            $default_grade[$recMethod] = $allGradeArray;
                        } else {
                            $default_grade[$recMethod] = [];
                        }
                    }
                }

                $currencyDefault = DB::table('acc_currency')
                    ->leftJoin('acc_received_default', 'acc_currency.received_default_fk', '=', 'acc_received_default.id')
                    ->select(
                        'acc_currency.name as currency_name',
                        'acc_currency.id as currency_id',
                        'acc_currency.rate',
                        'default_grade_id',
                        'acc_received_default.name as method_name'
                    )
                    ->orderBy('acc_currency.id')
                    ->get();
                $currency_default_grade = [];
                foreach ($currencyDefault as $default) {
                    $currency_default_grade[$default->default_grade_id][] = [
                        'currency_id' => $default->currency_id,
                        'currency_name' => $default->currency_name,
                        'rate' => $default->rate,
                        'default_grade_id' => $default->default_grade_id,
                    ];
                }
                // grade process end

                $cheque_status = ChequeStatus::get_key_value();

                return view('cms.commodity.order.ro_review', [
                    'form_action' => route('cms.order.ro-review', ['id' => request('id')]),
                    'received_order' => $received_order,
                    'order' => $order,
                    'order_list_data' => $order_list_data,
                    'received_data' => $received_data,
                    'undertaker' => $undertaker,
                    'product_grade_name' => $product_grade_name,
                    // 'logistics_grade_name'=>$logistics_grade_name,
                    'debit' => $debit,
                    'credit' => $credit,
                    'card_type' => $card_type,
                    'checkout_area' => $checkout_area,
                    'cheque_status' => $cheque_status,
                    'credit_card_grade' => $default_grade[ReceivedMethod::CreditCard],
                    'cheque_grade' => $default_grade[ReceivedMethod::Cheque],
                    // 'default_grade'=>$default_grade,
                    // 'currency_default_grade'=>$currency_default_grade,

                    'breadcrumb_data' => ['id' => $order->id, 'sn' => $order->sn],
                ]);
            }
        }
    }

    public function ro_taxation(Request $request, $id)
    {
        $request->merge([
            'id' => $id,
        ]);
        $request->validate([
            'id' => 'required|exists:ord_orders,id',
        ]);

        $received_order_collection = ReceivedOrder::where([
            'source_type' => app(Order::class)->getTable(),
            'source_id' => $id,
        ]);

        $received_order_data = $received_order_collection->get();
        if (count($received_order_data) == 0 || !$received_order_collection->first()->balance_date) {
            return abort(404);
        }

        $received_order = $received_order_collection->first();

        if ($request->isMethod('post')) {
            $request->validate([
                'received' => 'required|array',
                'product_grade_id' => 'required|exists:acc_all_grades,id',
                'product' => 'required|array',
                'logistics_grade_id' => 'nullable|exists:acc_all_grades,id',
                'order_dlv' => 'nullable|array',
                'discount' => 'nullable|array',
            ]);

            DB::beginTransaction();

            try {
                $received_order->update([
                    'logistics_grade_id' => request('logistics_grade_id'),
                    'product_grade_id' => request('product_grade_id'),
                ]);

                if (request('received') && is_array(request('received'))) {
                    $received = request('received');
                    foreach ($received as $key => $value) {
                        $value['received_id'] = $key;
                        ReceivedOrder::update_received($value);
                    }
                }

                if (request('product') && is_array(request('product'))) {
                    $product = request('product');
                    foreach ($product as $key => $value) {
                        $value['product_id'] = $key;
                        Product::update_product_taxation($value);
                    }
                }

                if (request('order_dlv') && is_array(request('order_dlv'))) {
                    $order = request('order_dlv');
                    foreach ($order as $key => $value) {
                        $value['order_id'] = $key;
                        Order::update_dlv_taxation($value);
                    }
                }

                if (request('discount') && is_array(request('discount'))) {
                    $discount = request('discount');
                    foreach ($discount as $key => $value) {
                        $value['discount_id'] = $key;
                        Discount::update_order_discount_taxation($value);
                    }
                }

                DB::commit();
                wToast(__('摘要/稅別更新成功'));

            } catch (\Exception $e) {
                DB::rollback();
                wToast(__('摘要/稅別更新失敗', ['type' => 'danger']));
            }

            return redirect()->route('cms.order.ro-receipt', ['id' => request('id')]);

        } else if ($request->isMethod('get')) {

            $order = Order::findOrFail(request('id'));
            $order_list_data = OrderItem::item_order(request('id'))->get();
            $order_discount = DB::table('ord_discounts')->where([
                'order_type' => 'main',
                'order_id' => request('id'),
            ])->where('discount_value', '>', 0)->get()->toArray();

            $received_data = ReceivedOrder::get_received_detail($received_order_data->pluck('id')->toArray());

            $total_grades = GeneralLedger::total_grade_list();

            return view('cms.commodity.order.ro_taxation', [
                'form_action' => route('cms.order.ro-taxation', ['id' => request('id')]),
                'received_order' => $received_order,
                'order' => $order,
                'order_discount' => $order_discount,
                'order_list_data' => $order_list_data,
                'received_data' => $received_data,
                'total_grades' => $total_grades,

                'breadcrumb_data' => ['id' => $order->id, 'sn' => $order->sn],
            ]);
        }
    }

    public function logistic_po(Request $request, $id, $sid)
    {
        $request->merge([
            'id' => $id,
            'sid' => $sid,
        ]);

        $request->validate([
            'id' => 'required|exists:ord_orders,id',
            'sid' => 'required|exists:ord_sub_orders,id',
        ]);

        $source_type = app(Order::class)->getTable();
        $type = 1;

        $paying_order = PayingOrder::where([
            'source_type' => $source_type,
            'source_id' => $id,
            'source_sub_id' => $sid,
            'type' => $type,
            'deleted_at' => null,
        ])->first();

        $order = Order::orderDetail($id)->get()->first();
        $sub_order = Order::subOrderDetail($id, $sid, true)->get()->toArray()[0];
        $supplier = Supplier::find($sub_order->supplier_id);

        if (!$paying_order) {
            $price = $sub_order->logistic_cost;
            $product_grade = PayableDefault::where('name', '=', 'product')->first()->default_grade_id;
            $logistics_grade = PayableDefault::where('name', '=', 'logistics')->first()->default_grade_id;

            $result = PayingOrder::createPayingOrder(
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
                $supplier ? $supplier->id : null,
                $supplier ? ($supplier->nickname ? $supplier->name . ' - ' . $supplier->nickname : $supplier->name) : null,
                $supplier ? $supplier->contact_tel : null,
                $supplier ? $supplier->contact_address : null
            );

            $paying_order = PayingOrder::findOrFail($result['id']);
        }

        $applied_company = DB::table('acc_company')->where('id', 1)->first();

        $logistics_grade_name = AllGrade::find($paying_order->logistics_grade_id)->eachGrade->code . ' ' . AllGrade::find($paying_order->logistics_grade_id)->eachGrade->name;

        if ($sub_order->projlgt_order_sn) {
            $logistics_grade_name = $logistics_grade_name . ' ' . $sub_order->ship_group_name . ' #' . $sub_order->projlgt_order_sn;
        } else {
            $logistics_grade_name = $logistics_grade_name . ' ' . $sub_order->ship_group_name . ' #' . $sub_order->package_sn;
        }

        $payable_data = PayingOrder::get_payable_detail($paying_order->id);
        $data_status_check = PayingOrder::payable_data_status_check($payable_data);

        $accountant = User::whereIn('id', $payable_data->pluck('accountant_id_fk')->toArray())->get();
        $accountant = array_unique($accountant->pluck('name')->toArray());
        asort($accountant);

        $undertaker = User::find($paying_order->usr_users_id);

        $zh_price = num_to_str($paying_order->price);

        if ($paying_order && $paying_order->append_po_id) {
            $append_po = PayingOrder::find($paying_order->append_po_id);
            $paying_order->append_po_link = PayingOrder::paying_order_link($append_po->source_type, $append_po->source_id, $append_po->source_sub_id, $append_po->type);
        }

        $view = 'cms.commodity.order.logistic_po';
        if (request('action') == 'print') {
            $view = 'doc.print_order_logistic_pay';
        }

        return view($view, [
            'breadcrumb_data' => ['id' => $id, 'sn' => $order->sn],

            'paying_order' => $paying_order,
            'payable_data' => $payable_data,
            'data_status_check' => $data_status_check,
            'order' => $order,
            'sub_order' => $sub_order,
            'undertaker' => $undertaker,
            'applied_company' => $applied_company,
            'logistics_grade_name' => $logistics_grade_name,
            'accountant' => implode(',', $accountant),
            'zh_price' => $zh_price,
        ]);
    }

    public function logistic_po_create(Request $request, $id, $sid)
    {
        $request->merge([
            'id' => $id,
            'sid' => $sid,
        ]);

        $request->validate([
            'id' => 'required|exists:ord_orders,id',
            'sid' => 'required|exists:ord_sub_orders,id',
        ]);

        $source_type = app(Order::class)->getTable();
        $type = 1;

        $paying_order = PayingOrder::where([
            'source_type' => $source_type,
            'source_id' => $id,
            'source_sub_id' => $sid,
            'type' => $type,
            'deleted_at' => null,
        ])->first();

        if (!$paying_order) {
            return abort(404);
        }

        if ($request->isMethod('post')) {
            $request->merge([
                'pay_order_id' => $paying_order->id,
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
                return redirect()->route('cms.order.logistic-po', [
                    'id' => $id,
                    'sid' => $sid,
                ]);

            } else {
                return redirect()->route('cms.order.logistic-po-create', [
                    'id' => $id,
                    'sid' => $sid,
                ]);
            }

        } else {

            if ($paying_order->balance_date) {
                return abort(404);
            }

            $order = Order::orderDetail($id)->get()->first();
            $sub_order = Order::subOrderDetail($id, $sid, true)->get()->toArray()[0];
            $supplier = Supplier::find($sub_order->supplier_id);

            $logistics_grade_name = AllGrade::find($paying_order->logistics_grade_id)->eachGrade->code . ' ' . AllGrade::find($paying_order->logistics_grade_id)->eachGrade->name;

            $currency = DB::table('acc_currency')->find($paying_order->acc_currency_fk);
            if (!$currency) {
                $currency = (object) [
                    'name' => 'NTD',
                    'rate' => 1,
                ];
            }

            $payable_data = PayingOrder::get_payable_detail($paying_order->id);

            $tw_price = $paying_order->price - $payable_data->sum('tw_price');

            $total_grades = GeneralLedger::total_grade_list();

            return view('cms.commodity.order.logistic_po_create', [
                'breadcrumb_data' => ['id' => $id, 'sid' => $sid, 'sn' => $order->sn],
                'paying_order' => $paying_order,
                'payable_data' => $payable_data,
                'order' => $order,
                'sub_order' => $sub_order,
                'supplier' => $supplier,
                'logistics_grade_name' => $logistics_grade_name,
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

                'form_action' => Route('cms.order.logistic-po-create', ['id' => $id, 'sid' => $sid]),
                'method' => 'create',
                'transactTypeList' => AccountPayable::getTransactTypeList(),
                'chequeStatus' => PayableChequeStatus::get_key_value(),
            ]);
        }
    }

    public function return_pay_order(Request $request, $id, $sid = null)
    {
        $request->merge([
            'id' => $id,
            'sid' => $sid,
        ]);

        $request->validate([
            'id' => 'required|exists:ord_orders,id',
            'sid' => 'nullable|exists:ord_sub_orders,id',
        ]);

        $source_type = app(Order::class)->getTable();
        $type = 9;

        $paying_order = PayingOrder::where([
            'source_type' => $source_type,
            'source_id' => $id,
            'source_sub_id' => $sid,
            'type' => $type,
            'deleted_at' => null,
        ])->first();

        list($order, $sub_order) = $this->getOrderAndSubOrders($id, $sid);

        $buyer = Customer::leftJoin('usr_customers_address AS customer_add', function ($join) {
            $join->on('usr_customers.id', '=', 'customer_add.usr_customers_id_fk');
            $join->where([
                'customer_add.is_default_addr' => 1,
            ]);
        })->where([
            'usr_customers.email' => $order->email,
        ])->select(
            'usr_customers.id',
            'usr_customers.name',
            'usr_customers.phone AS phone',
            'usr_customers.email',
            'customer_add.address AS address'
        )->first();

        if ($sid) {
            $price = $sub_order[0]->discounted_price + $sub_order[0]->dlv_fee;

        } else {
            $price = $order->total_price;
        }

        if (!$paying_order) {
            $product_grade = ReceivedDefault::where('name', '=', 'product')->first()->default_grade_id;
            $logistics_grade = ReceivedDefault::where('name', '=', 'logistics')->first()->default_grade_id;

            $result = PayingOrder::createPayingOrder(
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
                $buyer->id,
                $buyer->name,
                $buyer->phone,
                $buyer->address
            );

            $paying_order = PayingOrder::findOrFail($result['id']);
        }

        $undertaker = User::find($paying_order->usr_users_id);
        $applied_company = DB::table('acc_company')->where('id', 1)->first();

        $product_grade_name = AllGrade::find($paying_order->product_grade_id)->eachGrade->code . ' ' . AllGrade::find($paying_order->product_grade_id)->eachGrade->name;
        $logistics_grade_name = AllGrade::find($paying_order->logistics_grade_id)->eachGrade->code . ' ' . AllGrade::find($paying_order->logistics_grade_id)->eachGrade->name;

        $order_discount = DB::table('ord_discounts')->where([
            'order_type' => 'main',
            'order_id' => request('id'),
        ])->where('discount_value', '>', 0)->get()->toArray();
        foreach ($order_discount as $value) {
            $value->account_code = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->code : '4000';
            $value->account_name = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->name : '無設定會計科目';
        }

        $payable_data = PayingOrder::get_payable_detail($paying_order->id);
        $data_status_check = PayingOrder::payable_data_status_check($payable_data);

        $accountant = User::whereIn('id', $payable_data->pluck('accountant_id_fk')->toArray())->get();
        $accountant = array_unique($accountant->pluck('name')->toArray());
        asort($accountant);

        $zh_price = num_to_str($paying_order->price);

        if ($paying_order && $paying_order->append_po_id) {
            $append_po = PayingOrder::find($paying_order->append_po_id);
            $paying_order->append_po_link = PayingOrder::paying_order_link($append_po->source_type, $append_po->source_id, $append_po->source_sub_id, $append_po->type);
        }

        $view = 'cms.commodity.order.return_pay_order';
        if (request('action') == 'print') {
            $view = 'doc.print_order_return_order_pay';
        }

        return view($view, [
            'breadcrumb_data' => ['id' => $id, 'sn' => $order->sn],

            'paying_order' => $paying_order,
            'payable_data' => $payable_data,
            'data_status_check' => $data_status_check,
            'order' => $order,
            'sub_order' => $sub_order,
            'order_discount' => $order_discount,
            'buyer' => $buyer,
            'undertaker' => $undertaker,
            'applied_company' => $applied_company,
            'product_grade_name' => $product_grade_name,
            'logistics_grade_name' => $logistics_grade_name,
            'accountant' => implode(',', $accountant),
            'zh_price' => $zh_price,
        ]);
    }

    public function return_pay_create(Request $request, $id, $sid = null)
    {
        $request->merge([
            'id' => $id,
            'sid' => $sid,
        ]);

        $request->validate([
            'id' => 'required|exists:ord_orders,id',
            'sid' => 'nullable|exists:ord_sub_orders,id',
        ]);

        $source_type = app(Order::class)->getTable();
        $type = 9;

        $paying_order = PayingOrder::where([
            'source_type' => $source_type,
            'source_id' => $id,
            'source_sub_id' => $sid,
            'type' => $type,
            'deleted_at' => null,
        ])->first();

        if (!$paying_order) {
            return abort(404);
        }

        if ($request->isMethod('post')) {
            $request->merge([
                'pay_order_id' => $paying_order->id,
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
                return redirect()->route('cms.order.return-pay-order', [
                    'id' => $id,
                    'sid' => $sid,
                ]);

            } else {
                return redirect()->route('cms.order.return-pay-create', [
                    'id' => $id,
                    'sid' => $sid,
                ]);
            }

        } else {

            if ($paying_order->balance_date) {
                return abort(404);
            }

            list($order, $sub_order) = $this->getOrderAndSubOrders($id, $sid);

            $buyer = Customer::leftJoin('usr_customers_address AS customer_add', function ($join) {
                $join->on('usr_customers.id', '=', 'customer_add.usr_customers_id_fk');
                $join->where([
                    'customer_add.is_default_addr' => 1,
                ]);
            })->where([
                'usr_customers.email' => $order->email,
            ])->select(
                'usr_customers.id',
                'usr_customers.name',
                'usr_customers.phone AS phone',
                'usr_customers.email',
                'customer_add.address AS address'
            )->first();

            $product_grade_name = AllGrade::find($paying_order->product_grade_id)->eachGrade->code . ' ' . AllGrade::find($paying_order->product_grade_id)->eachGrade->name;
            $logistics_grade_name = AllGrade::find($paying_order->logistics_grade_id)->eachGrade->code . ' ' . AllGrade::find($paying_order->logistics_grade_id)->eachGrade->name;

            $order_discount = DB::table('ord_discounts')->where([
                'order_type' => 'main',
                'order_id' => request('id'),
            ])->where('discount_value', '>', 0)->get()->toArray();
            foreach ($order_discount as $value) {
                $value->account_code = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->code : '4000';
                $value->account_name = AllGrade::find($value->discount_grade_id) ? AllGrade::find($value->discount_grade_id)->eachGrade->name : '無設定會計科目';
            }

            $currency = DB::table('acc_currency')->find($paying_order->acc_currency_fk);
            if (!$currency) {
                $currency = (object) [
                    'name' => 'NTD',
                    'rate' => 1,
                ];
            }

            $payable_data = PayingOrder::get_payable_detail($paying_order->id);

            $tw_price = $paying_order->price - $payable_data->sum('tw_price');

            $total_grades = GeneralLedger::total_grade_list();

            return view('cms.commodity.order.return_pay_create', [
                'breadcrumb_data' => ['id' => $id, 'sid' => $sid, 'sn' => $order->sn],
                'paying_order' => $paying_order,
                'payable_data' => $payable_data,
                'order' => $order,
                'sub_order' => $sub_order,
                'order_discount' => $order_discount,
                'buyer' => $buyer,
                'product_grade_name' => $product_grade_name,
                'logistics_grade_name' => $logistics_grade_name,
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

                'form_action' => Route('cms.order.return-pay-create', ['id' => $id, 'sid' => $sid]),
                'method' => 'create',
                'transactTypeList' => AccountPayable::getTransactTypeList(),
                'chequeStatus' => PayableChequeStatus::get_key_value(),
            ]);
        }
    }

    public function create_invoice(Request $request, $id)
    {
        $request->merge([
            'id' => $id,
        ]);

        $request->validate([
            'id' => 'required|exists:ord_orders,id',
        ]);

        $source_type = app(Order::class)->getTable();
        $inv = OrderInvoice::where([
            'source_type' => $source_type,
            'source_id' => $id,
        ])->first();
        $received_order = ReceivedOrder::where([
            'source_type' => $source_type,
            'source_id' => $id,
        ])->first();
        if (!$received_order || $inv) {
            return abort(404);
        }

        $order = Order::orderDetail($id)->first();
        $sub_order = Order::subOrderDetail($id)->get();
        foreach ($sub_order as $key => $value) {
            $sub_order[$key]->items = json_decode($value->items);
            $sub_order[$key]->consume_items = json_decode($value->consume_items);
        }

        $valid_arr = OrderInvoice::where([
            'source_type' => $source_type,
            'merge_source_id' => null,
            'invoice_id' => null,
            'status' => 9,
        ])->pluck('source_id')->toArray();
        $merge_source = Order::where('id', '!=', $id)->whereIn('id', $valid_arr)->get();

        $order_discount = DB::table('ord_discounts')->where([
            'order_type' => 'main',
            'order_id' => $id,
        ])->where('discount_value', '>', 0)->get()->toArray();

        return view('cms.commodity.order.invoice', [
            'breadcrumb_data' => ['id' => $id, 'sn' => $order->sn],
            'form_action' => Route('cms.order.store-invoice', ['id' => $id]),

            'order' => $order,
            'sub_order' => $sub_order,
            'merge_source' => $merge_source,
            'unit' => [],
            'order_discount' => $order_discount,
            'received_order' => $received_order,
        ]);
    }

    public function store_invoice(Request $request, $id)
    {
        $request->merge([
            'id' => $id,
        ]);

        $request->validate([
            'id' => 'required|exists:ord_orders,id',
            'status' => 'required|in:1,9',
            'merge_source' => 'nullable|array',
            'merge_source.*' => 'exists:ord_orders,id',
            'category' => 'required|in:B2B,B2C',
            'buyer_ubn' => 'required_if:category,==,B2B',
            'buyer_name' => 'required|string|max:60',
            'buyer_email' => 'nullable|required_if:carrier_type,==,2|email:rfc,dns',
            'buyer_address' => 'required_if:invoice_method,==,print',
            'invoice_method' => 'required|in:print,give,e_inv',
            'love_code' => 'required_if:invoice_method,==,give',
            'carrier_type' => 'required_if:invoice_method,==,e_inv|in:0,1,2',
            'carrier_num' => 'required_if:carrier_type,==,0|required_if:carrier_type,==,1',
            'carrier_email' => 'required_if:carrier_type,==,2',
            'create_status_time' => 'nullable|date|date_format:Y-m-d',
        ]);

        $data = $request->except('_token');
        $result = OrderInvoice::create_invoice(app(Order::class)->getTable(), $id, $data);

        if ($result) {
            $parm = [
                'order_id' => $id,
                'gui_number' => $result->buyer_ubn,
                'invoice_category' => '電子發票',
                'invoice_number' => $result->invoice_number,
            ];
            Order::update_invoice_info($parm);

            // wToast(__('發票開立成功'));
            // if($result->r_msg){
            //     wToast(__($result->r_msg));
            // }
            return redirect()->route('cms.order.show-invoice', [
                'id' => $id,
            ]);

        } else {
            // wToast(__('發票開立失敗', ['type'=>'danger']));
            return redirect()->back();
        }
    }

    public function _order_detail(Request $request)
    {
        $request->merge([
            'order_id' => request('order_id'),
            'order_id.*' => explode(',', request('order_id')),
        ]);

        $request->validate([
            'order_id' => 'required|string',
            'order_id.*' => 'required|exists:ord_orders,id',
        ]);

        $data = [];
        $order_id_arr = explode(',', request('order_id'));

        foreach ($order_id_arr as $o_id) {
            $n_r_order = ReceivedOrder::where([
                'source_type' => app(Order::class)->getTable(),
                'source_id' => $o_id,
            ])->first();
            $n_order = Order::orderDetail($o_id)->first();
            $n_sub_order = Order::subOrderDetail($o_id)->get();
            foreach ($n_sub_order as $key => $value) {
                $n_sub_order[$key]->items = json_decode($value->items);
                $n_sub_order[$key]->consume_items = json_decode($value->consume_items);
            }
            $n_order_discount = DB::table('ord_discounts')->where([
                'order_type' => 'main',
                'order_id' => $o_id,
            ])->where('discount_value', '>', 0)->get()->toArray();

            foreach ($n_sub_order as $s_value) {
                foreach ($s_value->items as $i_value) {
                    $data[] = [
                        'received_sn' => $n_r_order->sn,
                        'name' => $i_value->product_title,
                        'count' => $i_value->qty,
                        'price' => number_format($i_value->price),
                        'amt' => number_format($i_value->total_price),
                        'tax' => $i_value->product_taxation == 1 ? '應稅' : '免稅',
                    ];
                }
            }
            if ($n_order->dlv_fee > 0) {
                $data[] = [
                    'received_sn' => $n_r_order->sn,
                    'name' => '物流費用',
                    'count' => 1,
                    'price' => number_format($n_order->dlv_fee),
                    'amt' => number_format($n_order->dlv_fee),
                    'tax' => $n_order->dlv_taxation == 1 ? '應稅' : '免稅',
                ];
            }
            foreach ($n_order_discount as $d_value) {
                $data[] = [
                    'received_sn' => $n_r_order->sn,
                    'name' => $d_value->title,
                    'count' => 1,
                    'price' => -number_format($d_value->discount_value),
                    'amt' => -number_format($d_value->discount_value),
                    'tax' => $d_value->discount_taxation == 1 ? '應稅' : '免稅',
                ];
            }
        }

        return response()->json($data);
    }

    public function show_invoice(Request $request, $id)
    {
        $request->merge([
            'id' => $id,
        ]);

        $request->validate([
            'id' => 'required|exists:ord_orders,id',
        ]);

        $source_type = app(Order::class)->getTable();
        $invoice = OrderInvoice::where([
            'source_type' => $source_type,
            'source_id' => $id,
        ])->first();
        if (!$invoice) {
            return abort(404);
        }

        $handler = User::find($invoice->user_id);

        // $order = Order::orderDetail($id)->first();
        // $sub_order = Order::subOrderDetail($id)->get();
        // foreach ($sub_order as $key => $value) {
        //     $sub_order[$key]->items = json_decode($value->items);
        //     $sub_order[$key]->consume_items = json_decode($value->consume_items);
        // }

        return view('cms.commodity.order.invoice_detail', [
            'breadcrumb_data' => ['id' => $id, 'sn' => $invoice->merchant_order_no],

            'invoice' => $invoice,
            'handler' => $handler,
            // 'order' => $order,
            // 'sub_order' => $sub_order,
        ]);
    }

    public function re_send_invoice(Request $request, $id)
    {
        $request->merge([
            'id' => $id,
        ]);
        $request->validate([
            'id' => 'required|exists:ord_order_invoice,id',
        ]);
        $inv_result = OrderInvoice::invoice_issue_api($id);

        if ($inv_result->source_type == app(Order::class)->getTable() && $inv_result->r_msg == 'SUCCESS') {
            $parm = [
                'order_id' => $inv_result->source_id,
                'gui_number' => $inv_result->buyer_ubn,
                'invoice_category' => '電子發票',
                'invoice_number' => $inv_result->invoice_number,
            ];
            Order::update_invoice_info($parm);
        }

        wToast(__($inv_result->r_msg));
        return redirect()->back();
    }
    // 獎金毛利
    public function bonus_gross(Request $request, $id)
    {
        $order = Order::orderDetail($id)->first();
        // OrderProfit::changeOwner(25,1,1);

        $dividend = CustomerDividend::where('category', DividendCategory::Order())
            ->where('category_sn', $order->sn)
            ->where('type', 'get')->get()->first();

        // dd(OrderItem::itemList($id,['profit'=>1])->get()->toArray());

        $dataList = OrderItem::itemList($id, ['profit' => 1])->get();
        $bonus = [0, 0];
        foreach ($dataList as $value) {
            $bonus[0] = $bonus[0] += $value->bonus;
            $bonus[1] = $bonus[1] += $value->bonus2;
        }

        if ($dividend) {
            $dividend = $dividend->dividend;
        } else {
            $dividend = 0;
        }
        //   dd(OrderProfitLog::dataList($id)->orderBy('created_at', 'DESC')->get());
        // dd(OrderProfitLog::dataList($id)->get());
        return view('cms.commodity.order.bonus_gross', [
            'id' => $id,
            'order' => $order,
            'dataList' => $dataList,
            'discounts' => Discount::orderDiscountList('main', $id)->get()->toArray(),
            'dividend' => $dividend,
            'log' => OrderProfitLog::dataList($id)->orderBy('created_at', 'DESC')->get(),
            'breadcrumb_data' => ['id' => $id, 'sn' => $order->sn],
            'bonus' => $bonus,
        ]);
    }

    // 個人獎金
    public function personal_bonus(Request $request, $id)
    {
        $order = Order::orderDetail($id)->first();
        $customer_id = $request->user()->customer_id;
        $dataList = OrderProfit::dataList($id, $customer_id)->get();
        //  dd($dataList);
        // dd(OrderProfitLog::dataListPerson($id, $user_id)->get());
        return view('cms.commodity.order.personal_bonus', [
            'id' => $id,
            'order' => $order,
            'dataList' => $dataList,
            'log' => OrderProfitLog::dataListPerson($id, $customer_id)->get(),
            'breadcrumb_data' => ['id' => $id, 'sn' => $order->sn],
        ]);
    }

    // 變更分潤持有者
    public function change_bonus_owner(Request $request, $id)
    {

        $request->validate([
            'customer_id' => 'required',
        ]);

        $customer_id = $request->input('customer_id');

        OrderProfit::changeOwner($id, $customer_id, $request->user()->id);

        return redirect()->back();
    }

    // 取消訂單
    public function cancel_order(Request $request, $id)
    {

        Order::cancelOrder($id, 'backend');

        wToast('訂單已經取消');

        return redirect()->back();
    }

    // 分割訂單
    public function split_order(Request $request, $id)
    {
        Order::checkCanSplit($id);
        list($order, $subOrder) = $this->getOrderAndSubOrders($id);

        if (!$order) {
            return abort(404);
        }
        //  dd($subOrder);

        return view('cms.commodity.order.split_order', [
            'breadcrumb_data' => ['id' => $id, 'sn' => $order->sn],
            'subOrders' => $subOrder,
            'order' => $order,
        ]);
    }
    // 儲存
    public function update_split_order(Request $request, $id)
    {

        $request->validate([
            'style_id' => 'required|array',
            'qty' => 'required|array',
        ]);

        $d = $request->all();
        $items = [];
        foreach ($d['style_id'] as $key => $style) {
            $items[$style] = $d['qty'][$key];
        }

        Order::splitOrder($id, $items, $request->user());
        wToast('分割完成');
        return redirect()->back();
    }

    public function editItem(Request $request, $id)
    {

        Order::checkCanSplit($id);
        list($order, $subOrder) = $this->getOrderAndSubOrders($id);

        if (!$order) {
            return abort(404);
        }

        $addr = DB::table('ord_address')->where('order_id', $id)->get();
        $address = [];

        foreach ($addr as $value) {
            $address[$value->type] = $value;
            $address[$value->type]->default_region = Addr::getRegions($value->city_id);
        }

        $shipmentCategory = ShipmentCategory::whereIn('code', ['deliver', 'pickup'])->get();

        $shipEvent = [];

        //  dd(Depot::get()->toArray());
        foreach ($shipmentCategory as $value) {

            switch ($value->code) {
                case "deliver":
                    $arr = ShipmentGroup::select('id', 'name')->get();
                    break;
                case "pickup":
                    $arr = Depot::select('id', 'name')->get();
                    break;
                case "family":
                    $arr = [];
                    break;
            }

            $shipEvent[$value->code] = $arr;
        }

        return view('cms.commodity.order.edit_old_order', [
            'breadcrumb_data' => ['id' => $id, 'sn' => $order->sn],
            'subOrders' => $subOrder,
            'order' => $order,
            'addr' => $address,
            'citys' => Addr::getCitys(),
            'shipmentCategory' => $shipmentCategory,
            'shipEvent' => $shipEvent,
        ]);
    }

    public function updateItem(Request $request, $id)
    {

        $request->validate([
            'item_id' => 'required|array',
            'note' => 'required|array',
        ]);

        $d = $request->all();
        DB::beginTransaction();
        foreach ($d['item_id'] as $key => $value) {
            if (isset($d['note'][$key])) {
                OrderItem::where('id', $value)->update([
                    'note' => $d['note'][$key],
                ]);
            }
        }

        $prefix = [
            'orderer' => 'ord',
            'receiver' => 'rec',
            'sender' => 'sed',
        ];

        foreach ($prefix as $pre) {
            $city = Addr::where('id', $d[$pre . "_city_id"])->get()->first();
            $region = Addr::where('id', $d[$pre . "_region_id"])->get()->first();

            DB::table('ord_address')->where('id', $d[$pre . "_id"])->update(
                [
                    'city_id' => isset($city->id) ? $city->id : 0,
                    'city_title' => isset($city->title) ? $city->title : '',
                    'region_id' => isset($region->id) ? $region->id : 0,
                    'region_title' => isset($region->title) ? $region->title : '',
                    'addr' => $d[$pre . "_addr"],
                    'address' => isset($region->id) ? Addr::fullAddr($region->id, $d[$pre . "_addr"]) : $d[$pre . "_addr"],
                    'zipcode' => isset($region->zipcode) ? $region->zipcode : '',
                    'name' => $d[$pre . "_name"],
                    'phone' => $d[$pre . "_phone"],
                ]
            );
        }

        // Addr::fullAddr()
        $total_dlv_fee = 0;
        foreach ($d['sub_order_id'] as $key => $value) {

            $sCategory = ShipmentCategory::where('code', $d['ship_category'][$key])->get()->first();

            switch ($d['ship_category'][$key]) {
                case "deliver":
                    $ship_event = ShipmentGroup::where('id', $d['ship_event_id'][$key])->get()->first()->name;
                    break;
                case "pickup":
                    $ship_event = Depot::where('id', $d['ship_event_id'][$key])->get()->first()->name;
                    break;
                default:
                    $ship_event = '全家';
            }

            SubOrders::where('id', $value)->update([
                'ship_category' => $d['ship_category'][$key],
                'ship_category_name' => $sCategory->category,
                'dlv_fee' => $d['dlv_fee'][$key],
                'ship_event_id' => isset($d['ship_event_id'][$key]) ? $d['ship_event_id'][$key] : 0,
                'ship_event' => $ship_event,
                'note' => $d['sub_order_note'][$key],
            ]);

            $total_dlv_fee += $d['dlv_fee'][$key];
        }
        $order = Order::where('id', $id)->get()->first();
        $total_price = $order->total_price - $order->dlv_fee + $total_dlv_fee;

        Order::where('id', $id)->update([
            'dlv_fee' => $total_dlv_fee,
            'total_price' => $total_price,
            'note' => $d['order_note'],
        ]);
        
        DB::commit();
        wToast('修改完成');

        return redirect(route('cms.order.detail', ['id' => $id]));

    }

    public function order_flow(Request $request, $id)
    {
        $query = $request->query();
        $page = getPageCount(Arr::get($query, 'data_per_page'));

        $order = DB::table(app(Order::class)->getTable() . ' as order')
            ->where('order.id', $id)
            ->get()->first();

        if (!$order) {
            return abort(404);
        }

        $dataList = DB::table(app(OrderFlow::class)->getTable() . ' as flow')
            ->where('flow.order_id', $id)
            ->orderByDesc('flow.id');
        $dataList = $dataList->paginate($page)->appends($query);
        return view('cms.commodity.order.order_flow', [
            'order' => $order,
            'dataList' => $dataList,
            'data_per_page' => $page,
            'breadcrumb_data' => ['id' => $id, 'sn' => $order->sn],
        ]);
    }
}
