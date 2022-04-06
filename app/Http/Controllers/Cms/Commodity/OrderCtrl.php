<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Order\UserAddrType;
use App\Http\Controllers\Controller;
use App\Models\Addr;
use App\Models\Customer;
use App\Models\CustomerIdentity;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderCart;
use App\Models\OrderStatus;
use App\Models\SaleChannel;
use App\Models\ShipmentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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

            $cart = OrderCart::cartFormater($oldData, false);
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
            $salechannels = CustomerIdentity::getSalechannels($customer_id, [1])->get()->toArray();
        } else {
            $salechannels = [];
        }

        $citys = Addr::getCitys();

        //  dd(Discount::getDiscounts('global-normal'));
        //    dd($citys);
        return view('cms.commodity.order.edit', [
            'customer_id' => $customer_id,
            'customers' => Customer::get(),
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
                case 'reciver':
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
                case 'reciver':
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

        $re = Order::createOrder($customer->email, 1, $address, $items, $d['note'], $coupon);
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
                        case UserAddrType::reciver()->value:
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
        $subOrder = Order::subOrderDetail($id)->get()->toArray();

        //  dd(Discount::orderDiscountList('main',$id)->get()->toArray());

        foreach ($subOrder as $key => $value) {
            $subOrder[$key]->items = json_decode($value->items);
        }

        //    dd($order);

        if (!$order) {
            return abort(404);
        }
        //  dd( Discount::orderDiscountList('main', $id)->get()->toArray());

        $sn = $order->sn;
        return view('cms.commodity.order.detail', [
            'sn' => $sn,
            'order' => $order,
            'subOrders' => $subOrder,
            'breadcrumb_data' => $sn,
            'subOrderId' => $subOrderId,
            'discounts' => Discount::orderDiscountList('main', $id)->get()->toArray(),
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
}
