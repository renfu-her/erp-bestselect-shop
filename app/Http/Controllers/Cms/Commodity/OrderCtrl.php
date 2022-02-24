<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Order\UserAddrType;
use App\Http\Controllers\Controller;
use App\Models\Addr;
use App\Models\Customer;
use App\Models\Order;
use App\Models\SaleChannel;
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

        // dd(Order::orderList()->get()->toArray());

        $query = $request->query();
        $cond = [];
        $page = getPageCount(Arr::get($query, 'data_per_page', 10));
        $cond['keyword'] = Arr::get($query, 'keyword', null);
        $dataList = Order::orderList($cond['keyword'])->paginate($page)->appends($query);

        return view('cms.commodity.order.list', [
            'dataList' => $dataList,
            'cond' => $cond,
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
        // dd(get_class($request->user()));
        // OrderCart::productAdd(1, get_class($request->user()), 1, 1, 1, 1, 1);
        // $items = OrderCart::productList($request->user()->id, get_class($request->user()))->get()->toArray();

        $customer_id = $request->user()->customer_id;

        $citys = Addr::getCitys();

        return view('cms.commodity.order.edit', [
            //  'items' => $items,
            'customer_id' => $customer_id,
            'customers' => Customer::get(),
            'citys' => $citys,
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

        $re = Order::createOrder($customer->email, 1, $address, $items);
        if ($re['success'] == '1') {
            wToast('訂單新增成功');
            return redirect(route('cms.order.index'));
        }

        return redirect()->back();
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
     * @return \Illuminate\Http\Response
     */
    public function detail($id)
    {

        $order = Order::orderDetail($id)->get()->first();
        $subOrder = Order::subOrderDetail($id)->get()->toArray();


        foreach ($subOrder as $key => $value) {
            $subOrder[$key]->items = json_decode($value->items);
        }

   //  dd($subOrder);

        if (!$order) {
            return abort(404);
        }
        //   dd($order);

        $sn = $order->sn;
        return view('cms.commodity.order.detail', [
            'sn' => $sn,
            'order' => $order,
            'subOrders' => $subOrder,
            'breadcrumb_data' => $sn]);
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
