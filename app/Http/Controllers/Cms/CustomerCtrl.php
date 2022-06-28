<?php

namespace App\Http\Controllers\Cms;

use App\Enums\Customer\AccountStatus;
use App\Enums\Customer\Newsletter;
use App\Enums\Delivery\Event;
use App\Http\Controllers\Controller;
use App\Models\Addr;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerCoupon;
use App\Models\CustomerDividend;
use App\Models\CustomerLogin;
use App\Models\Delivery;
use App\Models\LogisticFlow;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $query = $request->query();
        $name = Arr::get($query, 'name', '');
        $email = Arr::get($query, 'email', '');
        $customer = Customer::getCustomerBySearch($name)->paginate(10)->appends($query);

        return view('cms.admin.customer.list', [
            'name' => $name,
            'email' => $email,
            "dataList" => $customer,
            'formAction' => Route('cms.customer.index'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request)
    {
        $query = $request->query();

        $recentCity = $request->old('city_id');
        $bind = Arr::get($query, 'bind');
        $data = [];
        if ($bind) {
            $data = $request->user();
        }

        $regions = [];
        if ($recentCity) {
            $regions = Addr::getRegions($recentCity);
        }

        return view('cms.admin.customer.edit', [
            'method' => 'create',
            'formAction' => Route('cms.customer.create'),
            'customer_list' => Customer::all(),
            'citys' => Addr::getCitys(),
            'regions' => $regions,
            'bind' => $bind,
            'data' => $data,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'password' => 'confirmed|min:4', 'name' => 'required|string',
            'email' => ['required', 'email:rfc,dns', 'unique:App\Models\Customer'],
        ]);

        $uData = $request->only('email', 'name', 'password'
            , 'phone', 'birthday', 'sex', 'acount_status'
        );

        Customer::createCustomer($uData['name']
            , $uData['email']
            , $uData['password']
            , $uData['phone'] ?? null
            , $uData['birthday'] ?? null
            , $uData['sex'] ?? null
            , $uData['acount_status'] ?? AccountStatus::close()->value
        );

        if ($request->input('bind') == '1') {
            User::customerBinding($request->user()->id, $uData['email']);
            wToast('新增並綁定完成');
            return redirect(Route('cms.usermnt.customer-binding'));
        }

        wToast('新增完成');
        return redirect(Route('cms.customer.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function edit(Request $request, $id)
    {
        $data = Customer::getCustomer($id)->get()->first();
        $loginMethods = CustomerLogin::where('usr_customers_id_fk', '=', $id)
                                        ->select('login_method')
                                        ->get();
        if (!$data) {
            return abort(404);
        }
        $recentCity = $request->old('city_id');
        $regions = [];
        if ($recentCity) {
            $regions = Addr::getRegions($recentCity);
        } else {
            $regions = Addr::getRegions($data['city_id']);
        }

        return view('cms.admin.customer.edit', [
            'method' => 'edit', 'id' => $id,
            'formAction' => Route('cms.customer.edit', ['id' => $id]),
            'data' => $data,
            'customer_list' => Customer::all(),
            'citys' => Addr::getCitys(),
            'regions' => $regions,
            'loginMethods' => $loginMethods,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $uData = $request->only('email', 'name', 'sex'
            , 'phone', 'birthday', 'acount_status', 'newsletter'
        );

        $updateArr = [
            'name' => $uData['name'],
            'sex' => $uData['sex'] ?? null,
            'phone' => $uData['phone'],
            'birthday' => $uData['birthday'] ?? null,
            'newsletter' => $uData['newsletter'] ?? Newsletter::subscribe()->value,
        ];
        $password = $request->input('password');
        if ($password) {
            $updateArr['password'] = Hash::make($password);
        }
        $acount_status = $request->input('acount_status');
        if (0 == $acount_status || 1 == $acount_status) {
            $updateArr['acount_status'] = $acount_status;
        }
        DB::transaction(function () use ($id, $updateArr
        ) {
            Customer::where('id', $id)->update($updateArr);
            return ['success' => 1, 'error_msg' => "", 'id' => $id];
        });

        wToast('檔案更新完成');
        return redirect(Route('cms.customer.edit', ['id' => $id]));
    }

    /**
     * @param  Request  $request
     * @param $id int 會員id
     * 我的優惠卷
     */
    public function coupon(Request $request, $id)
    {
        $query = $request->query();
        $order = Arr::get($query, 'order', '');
        $dataList = CustomerCoupon::getList($id);
        if (isset($order) && !empty($order)) {
            $dataList->orderByDesc('discount.end_date');
        }
        $dataList = $dataList->get();
        return view('cms.admin.customer.coupon', [
            'customer' => $id,
            'order' => $order,
            "dataList" => $dataList,
        ]);
    }

    /**
     * @param  Request  $request
     * @param $id int 會員id
     * 列出收件地址管理
     */
    public function address(Request $request, $id)
    {
        $defaultAddress = CustomerAddress::where('usr_customers_id_fk', '=', $id)
            ->where('is_default_addr', '=', 1)
            ->select('address')
            ->get()
            ->first();

        $otherAddress = CustomerAddress::where('usr_customers_id_fk', '=', $id)
            ->where('is_default_addr', '=', 0)
            ->select('address')
            ->get();

        return view('cms.admin.customer.address', [
            'customer' => $id,
            'defaultAddress' => $defaultAddress,
            'otherAddress' => $otherAddress,
        ]);

    }

    /**
     * @param  Request  $request
     * @param int $id 會員id
     * 會員專區：我的訂單
     * @return void
     */
    public function order(Request $request, $id)
    {
        $email = Customer::where('id', '=', $id)->select('email')->get()->first()->email;
        $orderIds = Order::where('email', '=', $email)->select('id')->get();

        $orders = [];
        foreach ($orderIds as $orderId) {
            $order = Order::orderDetail($orderId->id, $email)->get()->first();
            $subOrder = Order::subOrderDetail($orderId->id)->get()->toArray();

            $subOrderArray = array_map(function ($n) {
                $delivery = Delivery::getDeliveryWithEventWithSn(Event::order, $n->id)->select('id')->get()->first();
                $n->shipment_flow = LogisticFlow::getListByDeliveryId($delivery->id)->select('status', 'created_at')->get()->toArray();

                $n->items = json_decode($n->items);
//                foreach ($n->items as $key => $value) {
//                    if ($value->img_url) {
//                        $n->items[$key]->img_url = asset($n->items[$key]->img_url);
//                    } else {
//                        $n->items[$key]->img_url = '';
//                    }
//                    //convert string value to int type
//                    if ($value->qty) {
//                        $n->items[$key]->qty = intval($n->items[$key]->qty);
//                    }
//                    if ($value->total_price) {
//                        $n->items[$key]->total_price = intval($n->items[$key]->total_price);
//                    }
//                }

                //convert string value to int type
//                if ($n->dlv_fee) {
//                    $n->dlv_fee = intval($n->dlv_fee);
//                }
                return $n;
            }, $subOrder);

            $orders[] = [
                'order' => $order,
                'sub_order' => $subOrderArray,
            ];
        }

        return view('cms.admin.customer.order', [
            'customer' => $id,
            'orders' => $orders,
        ]);

    }

    /**
     * @param  Request  $request
     * 我的鴻利
     * @return void
     */
    public function dividend(Request $request, $id)
    {
//        $dividend = CustomerDividend::getDividend($id)->get()->first()->dividend;
        $typeGet = CustomerDividend::getList($id, 'get')->get();
        $typeUsed = CustomerDividend::getList($id, 'used')->get();

        return view('cms.admin.customer.dividend', [
            'customer' => $id,
//            'dividend' => $dividend,
            'get_record' => $typeGet,
            'use_record' => $typeUsed,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        Customer::deleteById($id);

        wToast('資料刪除完成');
        return redirect(Route('cms.customer.index'));
    }
}
