<?php

namespace App\Http\Controllers\Api\Web;

use App\Enums\Globals\ResponseParam;
use App\Enums\Order\UserAddrType;
use App\Enums\Received\ReceivedMethod;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderCtrl extends Controller
{
    //

    public function getGlobalDiscount(Request $request)
    {

        $dicount = Discount::getDiscounts('global-normal');

        $re = [];
        $re[ResponseParam::status()->key] = '0';
        $re[ResponseParam::msg()->key] = '';
        $re[ResponseParam::data()->key] = $dicount;
        return response()->json($re);

    }

    public function payinfo(Request $request)
    {

        $re = [];
        $re[ResponseParam::status()->key] = '0';
        $re[ResponseParam::msg()->key] = '';
        $re[ResponseParam::data()->key] = [
            ['id' => ReceivedMethod::Cash()->value,
                'name' => ReceivedMethod::Cash()->description],
            ['id' => ReceivedMethod::CreditCard()->value,
                'name' => ReceivedMethod::CreditCard()->description],
        ];
        return response()->json($re);

    }

    public function createOrder(Request $request)
    {
        $payLoad = json_decode(request()->getContent(), true);

        $validator = Validator::make($payLoad, [
            'email' => 'required|email',
            "orderer.name" => "required",
            "orderer.phone" => "required",
            "orderer.address" => "required",
            "orderer.city_id" => "required|numeric",
            "orderer.region_id" => "required|numeric",
            "orderer.addr" => "required", 
            "recipient.name" => "required",
            "recipient.phone" => "required",
            "recipient.address" => "required",
            "recipient.city_id" => "required|numeric",
            "recipient.region_id" => "required|numeric",
            "recipient.addr" => "required",
            "payment" => Rule::in([ReceivedMethod::Cash()->value, ReceivedMethod::CreditCard()->value]),
            "products" => 'array|required',
            "products.*.qty" => "required|numeric",
            "products.*.product_id" => "required",
            "products.*.product_style_id" => "required",
            "products.*.shipment_type" => "required",
            "products.*.shipment_event_id" => "required",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'message' => $validator->errors(),
            ]);
        }

        DB::beginTransaction();

        $customer = Customer::where('email', $payLoad['email'])->get()->first();

        if (!$customer) {
            $udata = [
                'name' => $payLoad['orderer']['name'],
                'email' => $payLoad['email'],
                'password' => '1234',
            ];

            Customer::createCustomer($udata['name'], $udata['email'], $udata['password']);
        }
        $address = [];
        $address[] = ['name' => $payLoad['orderer']['name'],
            'phone' => $payLoad['orderer']['phone'],
            'address' => $payLoad['orderer']['address'],
            'type' => UserAddrType::orderer()->value];

        $address[] = ['name' => $payLoad['orderer']['name'],
            'phone' => $payLoad['orderer']['phone'],
            'address' => $payLoad['orderer']['address'],
            'type' => UserAddrType::sender()->value];

        $address[] = ['name' => $payLoad['recipient']['name'],
            'phone' => $payLoad['recipient']['phone'],
            'address' => $payLoad['recipient']['address'],
            'type' => UserAddrType::receiver()->value];

        $re = Order::createOrder($payLoad['email'], 1, $address, $payLoad['products']);

        if ($re['success'] == '1') {
            DB::commit();
            return [
                'status' => '0',
                'message' => '',
                'data' => [
                    'order_id' => $re['order_id'],
                ],
            ];
        }

        DB::rollBack();
        return [
            'stauts' => 'E05',
            'message' => $re,
        ];
    }
}
