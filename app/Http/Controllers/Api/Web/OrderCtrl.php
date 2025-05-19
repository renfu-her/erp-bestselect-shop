<?php

namespace App\Http\Controllers\Api\Web;

use App\Enums\Delivery\Event;
use App\Enums\eTicket\ETicketVendor;
use App\Enums\Globals\ApiStatusMessage;
use App\Enums\Globals\ResponseParam;
use App\Enums\Order\InvoiceMethod;
use App\Enums\Order\UserAddrType;
// use App\Enums\;
use App\Enums\Received\ReceivedMethod;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Discount;
use App\Models\LogisticFlow;
use App\Models\Order;
use App\Models\OrderCreateLog;
use App\Models\OrderRemit;
use App\Models\ReceiveDepot;
use App\Models\TikYoubonOrder;
use App\Services\ThirdPartyApis\Youbon\YoubonOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            ['id' => ReceivedMethod::Remittance()->value,
                'name' => ReceivedMethod::Remittance()->description],
            ['id' => 'line_pay',
                'name' => 'Line Pay'],
        ];

        return response()->json($re);

    }

    public function createOrder(Request $request)
    {

        $payLoad = request()->getContent();

        if (!$payLoad) {
            OrderCreateLog::create([
                'email' => '',
                'payload' => '',
                'return_value' => '參數不能為空值',
                'success' => 0,
            ]);
            return response()->json([
                'status' => 'E01',
                'message' => '參數不能為空值',
            ]);
        }

        $payLoad = json_decode($payLoad, true);

        $valiRule = [
            'invoice_method' => 'required|in:print,give,e_inv',
            'love_code' => 'required_if:invoice_method,==,give',
            'carrier_type' => 'required_if:invoice_method,==,e_inv|in:0,1,2',
            'carrier_num' => 'required_if:invoice_method,==,e_inv',

            "orderer.name" => "required",
            "orderer.phone" => "required",
            "orderer.region_id" => "required|numeric",
            "orderer.addr" => "required",
            "recipient.name" => "required",
            "recipient.phone" => "required",
            "recipient.region_id" => "required|numeric",
            "recipient.addr" => "required",
            //  "payment" => Rule::in([ReceivedMethod::Cash()->value, ReceivedMethod::CreditCard()->value, ReceivedMethod::Remittance()->value, 'line_pay']),
            "products" => 'array|required',
            "products.*.qty" => "required|numeric",
            "products.*.product_id" => "required",
            "products.*.product_style_id" => "required",
            "products.*.shipment_type" => "required",
            "products.*.shipment_event_id" => "required",
        ];

        $checkPayment = false;
        if (!isset($payLoad['type'])) {
            $valiRule['payment'] = Rule::in([ReceivedMethod::Cash()->value, ReceivedMethod::CreditCard()->value, ReceivedMethod::Remittance()->value, 'line_pay']);
            $checkPayment = true;
        }

        if (!Auth::guard('sanctum')->check()) {
            $valiRule['email'] = 'required|email';
        }

        $validator = Validator::make($payLoad, $valiRule);

        if ($validator->fails()) {
            OrderCreateLog::create([
                'email' => '',
                'payload' => json_encode($payLoad),
                'return_value' => json_encode($validator->errors()),
                'success' => 0,
            ]);
            return response()->json([
                'status' => 'E01',
                'message' => $validator->errors(),
            ]);
        }

        DB::beginTransaction();

        if (!Auth::guard('sanctum')->check()) {

            $customer = Customer::where('email', $payLoad['email'])->get()->first();

            if (!$customer) {

                $udata = [
                    'name' => $payLoad['orderer']['name'],
                    'email' => $payLoad['email'],
                    'password' => '1234',
                ];

                Customer::createCustomer($udata['name'], $udata['email'], $udata['password']);

                $customer = Customer::where('email', $payLoad['email'])->get()->first();
            }
        } else {
            $customer = $request->user();
        }

        $address = [];
        $address[] = ['name' => $payLoad['orderer']['name'],
            'phone' => $payLoad['orderer']['phone'],
            'address' => $payLoad['orderer']['addr'],
            'city_id' => $payLoad['orderer']['city_id'],
            'region_id' => $payLoad['orderer']['region_id'],
            'type' => UserAddrType::orderer()->value];

        $address[] = ['name' => $payLoad['orderer']['name'],
            'phone' => $payLoad['orderer']['phone'],
            'address' => $payLoad['orderer']['addr'],
            'city_id' => $payLoad['orderer']['city_id'],
            'region_id' => $payLoad['orderer']['region_id'],
            'type' => UserAddrType::sender()->value];

        $address[] = ['name' => $payLoad['recipient']['name'],
            'phone' => $payLoad['recipient']['phone'],
            'address' => $payLoad['recipient']['addr'],
            'city_id' => $payLoad['recipient']['city_id'],
            'region_id' => $payLoad['recipient']['region_id'],
            'type' => UserAddrType::receiver()->value];

        $couponObj = null;
        if (isset($payLoad['coupon_type']) && isset($payLoad['coupon_sn'])) {
            $couponObj = [$payLoad['coupon_type'], $payLoad['coupon_sn']];
        }

        $payinfo = null;
        $payinfo['category'] = $payLoad['category'] ?? null;
        $payinfo['invoice_method'] = $payLoad['invoice_method'] ?? null;
        $payinfo['inv_title'] = $payLoad['inv_title'] ?? null;
        $payinfo['buyer_ubn'] = $payLoad['buyer_ubn'] ?? null;
        $payinfo['buyer_email'] = $payLoad['buyer_email'] ?? null;
        $payinfo['love_code'] = $payLoad['love_code'] ?? null;
        $payinfo['carrier_type'] = $payLoad['carrier_type'] ?? null;
        // 若為會員載具 前端官網不開放編輯修改功能 所以一律為預設email
        if (2 == $payinfo['carrier_type']) {
            $payinfo['buyer_email'] = $customer->email;
            $payinfo['carrier_num'] = $customer->email;
        } else {
            $payinfo['buyer_email'] = $customer->email;
            $payinfo['carrier_num'] = $payLoad['carrier_num'] ?? null;
        }

        $dividend = [];
        if (isset($payLoad['points']) && isset($payLoad['points'])) {
            $dividend = $payLoad['points'];
        }

        if (isset($payLoad['payment']) && $checkPayment) {
            if ($payLoad['payment'] == 'line_pay') {
                $payment = (object) [
                    'value' => 'line_pay',
                    'description' => 'Line Pay',
                ];

            } else {
                $payment = ReceivedMethod::fromValue($payLoad['payment']);
            }
        } else {
            $payment = null;
        }

        $sale_channel_id = 1;

        if (isset($payLoad['salechannel_id']) && $payLoad['salechannel_id']) {
            $sale_channel_id = $payLoad['salechannel_id'];
        }

        $re = Order::createOrder($customer->email, $sale_channel_id, $address, $payLoad['products'], $payLoad['mcode'] ?? null, $payLoad['note'], $couponObj, $payinfo, $payment, $dividend, $request->user());

        if ($re['success'] == '1') {
            DB::commit();
            // log
            OrderCreateLog::create([
                'email' => $customer->email,
                'payload' => json_encode($payLoad),
                'return_value' => json_encode($re),
                'success' => 1,
            ]);
            return [
                'status' => '0',
                'message' => '',
                'data' => [
                    'order_id' => $re['order_id'],
                ],
            ];
        }

        DB::rollBack();
        // log
        OrderCreateLog::create([
            'email' => $customer->email,
            'payload' => json_encode($payLoad),
            'return_value' => json_encode($re),
            'success' => 0,
        ]);
        return [
            'status' => 'E05',
            'message' => $re,
        ];
    }

    /**
     * @param  Request  $request
     * 訂單列表API
     * @return
     */
    public function orderList(Request $request)
    {
        $data['email'] = $request->user()->email;
        $orderIds = Order::where('email', '=', $data['email'])->select('id')
            ->orderByDesc('id') //倒序
            ->get();

        if (count($orderIds) === 0) {
            return response()->json([
                ResponseParam::status => ApiStatusMessage::Fail,
                ResponseParam::msg => "查無訂單",
                ResponseParam::data => [],
            ]);
        }

        $orders = [];
        foreach ($orderIds as $orderId) {
            $order = Order::orderDetail($orderId->id, $data['email'])->get()->first();
            $subOrder = Order::subOrderDetail($orderId->id)->get()->toArray();

            $subOrderArray = array_map(function ($n) {
                $delivery = Delivery::getDeliveryWithEventWithSn(Event::order, $n->id)->select('id')->get()->first();
                $n->shipment_flow = LogisticFlow::getListByDeliveryId($delivery->id)->select('status', 'created_at')->get()->toArray();

                $n->items = json_decode($n->items);
                foreach ($n->items as $key => $value) {
                    if ($value->img_url) {
                        $n->items[$key]->img_url = getImageUrl($n->items[$key]->img_url, true);
                    } else {
                        $n->items[$key]->img_url = '';
                    }
                    //convert string value to int type
                    if ($value->qty) {
                        $n->items[$key]->qty = intval($n->items[$key]->qty);
                    }
                    if ($value->total_price) {
                        $n->items[$key]->total_price = intval($n->items[$key]->total_price);
                    }
                }

                //convert string value to int type
                if ($n->dlv_fee) {
                    $n->dlv_fee = intval($n->dlv_fee);
                }
                return $n;
            }, $subOrder);

            $orders[] = [
                'id' => $order->id,
                'status' => $order->status,
                'sn' => $order->sn,
                'payment_status' => $order->payment_status,
                'created_at' => $order->created_at,
                'total_price' => $order->total_price,
                'sub_order' => $subOrderArray,
                'logistic_url' => env('LOGISTIC_URL') . 'guest/order-flow/',

            ];
        }

        return response()->json([
            ResponseParam::status => ApiStatusMessage::Succeed,
            ResponseParam::msg => '',
            ResponseParam::data => $orders,
        ]);
    }

    public function orderDetail(Request $request)
    {
        $valiRule = ['order_id' => 'required'];

        if (!Auth::guard('sanctum')->check()) {
            $valiRule['email'] = 'required|email';
        }

        $validator = Validator::make($request->all(), $valiRule);

        if ($validator->fails()) {
            $re = [];
            $re[ResponseParam::status()->key] = 'E01';
            $re[ResponseParam::msg()->key] = $validator->errors();

            return response()->json($re);
        }
        $d = $request->all();

        if (!Auth::guard('sanctum')->check()) {
            $email = $d['email'];
        } else {
            $email = $request->user()->email;
        }

        $order = Order::orderDetail($d['order_id'], $email)->get()->first();
        if (!$order) {
            $re = [];
            $re[ResponseParam::status()->key] = 'E04';
            $re[ResponseParam::msg()->key] = "查無訂單";

            return response()->json($re);
        }

        $subOrder = Order::subOrderDetail($d['order_id'])->get()->toArray();

        $youbonOrderService = new YoubonOrderService();
        $order->sub_order = array_map(function ($n) use ($youbonOrderService) {
            $delivery = Delivery::getDeliveryWithEventWithSn(Event::order()->value, $n->id)->get()->first();
            $isETicketOrder = $youbonOrderService->isETicketOrder($delivery->id);
            if ($isETicketOrder) {
                $ticketExchangeUrl = [];
                $youbon_items = [];
                $eticketList = ReceiveDepot::getETicketOrderList($delivery->id)->get()->toArray();
                foreach ($eticketList as $eticketData) {
                    if (ETicketVendor::YOUBON_CODE == $eticketData->tik_type_code) {
                        $youbon_items[] = $eticketData;
                    }
                }
                if (0 < count($youbon_items)) {
                    $latestTikYoubonOrder = TikYoubonOrder::where('delivery_id', $delivery->id)->orderBy('id', 'desc')->first();
                    if (null != $latestTikYoubonOrder) {
                        // 取得電子票券兌換網址
                        $ticketExchangeUrl[] = $latestTikYoubonOrder->weburl;
                    }
                }
                $n->ticketExchangeUrl = $ticketExchangeUrl;
            }
            $n->shipment_flow = LogisticFlow::getListByDeliveryId($delivery->id)->select('status', 'created_at')->get()->toArray();

            $n->items = json_decode($n->items);
            foreach ($n->items as $key => $value) {
                if ($value->img_url) {
                    $n->items[$key]->img_url = getImageUrl($n->items[$key]->img_url, true);
                } else {
                    $n->items[$key]->img_url = '';
                }
            }
            return $n;
        }, $subOrder);

        // credit card start
        include app_path() . '/Helpers/auth_mpi_mac.php';

        $arr_data = [
            'MerchantID' => $str_merchant_id,
            'TerminalID' => $str_terminal_id,
            'lidm' => $order->sn,
            'purchAmt' => $order->total_price,
            'txType' => '0',
            'Option' => 0,
            'Key' => $auth_key,
            'MerchantName' => mb_convert_encoding($order->sale_title, 'BIG5', ['BIG5', 'UTF-8']),
            'AuthResURL' => route('payment.credit-card-result', ['id' => $order->id]),
            'OrderDetail' => mb_convert_encoding($order->note, 'BIG5', ['BIG5', 'UTF-8']),
            'AutoCap' => '1',
            'Customize' => ' ',
            'debug' => '0',
        ];

        $str_mac_string = auth_in_mac($arr_data['MerchantID'], $arr_data['TerminalID'], $arr_data['lidm'], $arr_data['purchAmt'], $arr_data['txType'], $arr_data['Option'], $arr_data['Key'], $arr_data['MerchantName'], $arr_data['AuthResURL'], $arr_data['OrderDetail'], $arr_data['AutoCap'], $arr_data['Customize'], $arr_data['debug']);

        $str_url_enc = get_auth_urlenc($arr_data['MerchantID'], $arr_data['TerminalID'], $arr_data['lidm'], $arr_data['purchAmt'], $arr_data['txType'], $arr_data['Option'], $arr_data['Key'], $arr_data['MerchantName'], $arr_data['AuthResURL'], $arr_data['OrderDetail'], $arr_data['AutoCap'], $arr_data['Customize'], $str_mac_string, $arr_data['debug']);
        $order->str_url = $str_url;
        $order->str_mac_string = $str_mac_string;
        $order->str_mer_id = $str_mer_id;
        $order->str_url_enc = $str_url_enc;

        $order->logistic_url = env('LOGISTIC_URL') . 'guest/order-flow/';
        // credit card end

        // line pay
        $order->line_pay_url = route('payment.line-pay', ['source_type' => app(Order::class)->getTable(), 'source_id' => $order->id, 'unique_id' => $order->unique_id]);

        $re = [];
        $re[ResponseParam::status()->key] = '0';
        $re[ResponseParam::data()->key] = $order;

        return response()->json($re);
    }

    //消費者 建立訂單匯款資料
    public function create_remit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'name' => 'required|string',
            'price' => 'required|numeric|min:1',
            'remit_date' => 'required|date|date_format:Y-m-d',
            'bank_code' => 'required|numeric|regex:/^\d{5}$/',
        ]);

        if ($validator->fails()) {
            $re = [];
            $re[ResponseParam::status()->key] = 'E01';
            $re[ResponseParam::msg()->key] = $validator->errors();

            return response()->json($re);
        }

        $cr = OrderRemit::createRemit(request('id'), request('name'), request('price'), request('remit_date'), request('bank_code'));

        $re = [];
        if ($cr['success'] == '1') {
            $re[ResponseParam::status()->key] = '0';
            $re[ResponseParam::msg()->key] = '';
            $re[ResponseParam::data()->key] = [
                'id' => $cr['id'],
            ];
        } else {
            $re[ResponseParam::status()->key] = '1';
            $re[ResponseParam::msg()->key] = $cr['error_msg'];
            $re[ResponseParam::data()->key] = '';
        }

        return response()->json($re);
    }

    //消費者 修改訂單匯款資料
    public function store_remit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'name' => 'required|string',
            'price' => 'required|numeric|min:1',
            'remit_date' => 'required|date|date_format:Y-m-d',
            'bank_code' => 'required|numeric|regex:/^\d{5}$/',
        ]);

        if ($validator->fails()) {
            $re = [];
            $re[ResponseParam::status()->key] = 'E01';
            $re[ResponseParam::msg()->key] = $validator->errors();

            return response()->json($re);
        }

        $cr = OrderRemit::updateRemit(request('id'), request('name'), request('price'), request('remit_date'), request('bank_code'));

        $re = [];
        if ($cr['success'] == '1') {
            $re[ResponseParam::status()->key] = '0';
            $re[ResponseParam::msg()->key] = '';
            $re[ResponseParam::data()->key] = $cr['data'];
        } else {
            $re[ResponseParam::status()->key] = '1';
            $re[ResponseParam::msg()->key] = $cr['error_msg'];
            $re[ResponseParam::data()->key] = '';
        }

        return response()->json($re);
    }

    //消費者 取得訂單匯款資料
    public function get_remit(Request $request, $order_id)
    {
        $remit = OrderRemit::getData($order_id)->get()->first();
        $re = [];
        if (null == $remit) {
            $re[ResponseParam::status()->key] = 'E01';
            $re[ResponseParam::msg()->key] = '找不到資料';
            $re[ResponseParam::data()->key] = null;
        } else {
            $re[ResponseParam::status()->key] = '0';
            $re[ResponseParam::msg()->key] = '';
            $re[ResponseParam::data()->key] = $remit;
        }
        return response()->json($re);
    }

    // 取消訂單

    public function cancelOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $re = [];
            $re[ResponseParam::status()->key] = 'E01';
            $re[ResponseParam::msg()->key] = $validator->errors();

            return response()->json($re);
        }

        $d = $request->all();
        $reOCO = Order::cancelOrder($d['order_id'], 'frontend');
        if ($reOCO['success'] == 0) {
            $re = [];
            $re[ResponseParam::status()->key] = 'E02';
            $re[ResponseParam::msg()->key] = $reOCO['error_msg'];

            return response()->json($re);
        } else {
            return [
                'status' => '0',
            ];
        }

    }
}
