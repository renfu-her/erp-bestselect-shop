<?php

namespace App\Http\Controllers\Api\Web;

use App\Enums\Delivery\Event;
use App\Enums\Globals\ResponseParam;
use App\Enums\Order\OrderStatus;
use App\Enums\Order\UserAddrType;
// use App\Enums\;
use App\Enums\Received\ReceivedMethod;
use App\Http\Controllers\Controller;
use App\Models\Addr;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Discount;
use App\Models\LogisticFlow;
use App\Models\Order;
use App\Models\OrderFlow;
use App\Models\OrderPayCreditCard;
use App\Models\ReceivedDefault;
use App\Models\ReceivedOrder;
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
        ];
        return response()->json($re);

    }

    public function payment_credit_card(Request $request, $id, $unique_id)
    {
        $request->merge([
            'id' => $id,
            'unique_id' => $unique_id,
        ]);

        $request->validate([
            'id' => 'required|exists:ord_orders,id',
            // 'unique_id' => 'required|exists:ord_orders,unique_id',
        ]);

        $order = DB::table('ord_orders as order')
            ->leftJoin('usr_customers as customer', 'order.email', '=', 'customer.email')
            ->leftJoin('prd_sale_channels as sale', 'sale.id', '=', 'order.sale_channel_id')
            ->leftJoin('ord_received_orders as received', 'received.order_id', '=', 'order.id')
            ->select([
                'order.id',
                'order.sn',
                'order.discount_value',
                'order.discounted_price',
                'order.dlv_fee',
                'order.origin_price',
                'order.note',
                'order.status_code',
                'order.status',
                'order.total_price',
                'order.created_at',
                'order.unique_id',
                'customer.name',
                'customer.email',
                'sale.title as sale_title',
                'received.sn as received_sn',
            ])
            ->where([
                'order.id' => $id,
                'order.unique_id' => $unique_id,
                'received.balance_date' => null,
                'received.deleted_at' => null,
            ])
            ->where(function ($q) {
                $q->whereRaw('(order.status_code IN ("add"))');
            })
            ->first();

        if (!$order) {
            return abort(404);
        }

        include app_path() . '/Helpers/auth_mpi_mac.php';

        $str_mer_id = '77725';
        $str_merchant_id = '8220300000043';
        $str_terminal_id = '90300043';

        $str_url = 'https://testepos.ctbcbank.com/mauth/SSLAuthUI.jsp';

        $arr_data = [
            'MerchantID' => $str_merchant_id,
            'TerminalID' => $str_terminal_id,
            'lidm' => $order->sn,
            'purchAmt' => $order->total_price,
            'txType' => '0',
            'Option' => 0,
            'Key' => 'LPCvSznVxZ4CFjnWbtg4mUWo',
            'MerchantName' => mb_convert_encoding($order->sale_title, 'BIG5', ['BIG5', 'UTF-8']),
            'AuthResURL' => route('api.web.order.credit_card_checkout', ['id' => $id, 'unique_id' => $unique_id]),
            'OrderDetail' => mb_convert_encoding($order->note, 'BIG5', ['BIG5', 'UTF-8']),
            'AutoCap' => '1',
            'Customize' => ' ',
            'debug' => '0',
        ];

        $str_mac_string = auth_in_mac($arr_data['MerchantID'], $arr_data['TerminalID'], $arr_data['lidm'], $arr_data['purchAmt'], $arr_data['txType'], $arr_data['Option'], $arr_data['Key'], $arr_data['MerchantName'], $arr_data['AuthResURL'], $arr_data['OrderDetail'], $arr_data['AutoCap'], $arr_data['Customize'], $arr_data['debug']);

        $str_url_enc = get_auth_urlenc($arr_data['MerchantID'], $arr_data['TerminalID'], $arr_data['lidm'], $arr_data['purchAmt'], $arr_data['txType'], $arr_data['Option'], $arr_data['Key'], $arr_data['MerchantName'], $arr_data['AuthResURL'], $arr_data['OrderDetail'], $arr_data['AutoCap'], $arr_data['Customize'], $str_mac_string, $arr_data['debug']);

        return view('cms.frontend.checkout', [
            'order' => $order,
            'str_url' => $str_url,
            'str_mac_string' => $str_mac_string,
            'str_mer_id' => $str_mer_id,
            'str_url_enc' => $str_url_enc,
        ]);
    }

    /**
     * backend credit card checkout result
     *
     * @param  Request  $request
     * @param  int  $id primary ID of ord_orders
     *
     * @return reidrect
     */
    public function credit_card_checkout(Request $request, $id, $unique_id)
    {
        $EncArray = [];

        $received_order_collection = ReceivedOrder::where([
            'order_id'=>$id,
            'deleted_at'=>null,
        ]);
        $log = OrderPayCreditCard::where([
            'order_id'=>$id,
            'status'=>0,
        ])->orderBy('created_at', 'DESC')->first();
        if($received_order_collection->first() && !$log){
            return abort(404);
        }

        if ($request->isMethod('post')) {
            // avoid f5 reload
            if($log){
                return redirect()->route('api.web.order.credit_card_checkout', ['id' => $id, 'unique_id' => $unique_id]);
            }

            include app_path() . '/Helpers/auth_mpi_mac.php';

            $EncRes = request('URLResEnc') ? request('URLResEnc') : null;
            if ($EncRes) {
                $Key = 'LPCvSznVxZ4CFjnWbtg4mUWo';
                $debug = '0';
                $EncArray = gendecrypt($EncRes, $Key, $debug);

                if (is_array($EncArray) && count($EncArray) > 0) {
                    $status = isset($EncArray['status']) ? $EncArray['status'] : '';
                    $authAmt = isset($EncArray['authamt']) ? $EncArray['authamt'] : '';

                    if (empty($status) && $status == '0') {
                        if(! $received_order_collection->first()){
                            $received_order = ReceivedOrder::create_received_order($id);
                            $received_method = ReceivedMethod::CreditCard; // 'credit_card'

                            $data = [];
                            $data['acc_transact_type_fk'] = $received_method;
                            $data[$received_method]['installment'] = 'none';
                            $result_id = ReceivedOrder::store_received_method($data);

                            $parm = [];
                            $parm['received_order_id'] = $received_order->id;
                            $parm['received_method'] = $received_method;
                            $parm['received_method_id'] = $result_id;
                            $parm['grade_id'] = ReceivedDefault::where('name', $received_method)->first() ? ReceivedDefault::where('name', $received_method)->first()->default_grade_id : 0;
                            $parm['price'] = $authAmt;
                            ReceivedOrder::store_received($parm);
                        }
                    }

                    OrderPayCreditCard::create_log($id, (object) $EncArray);
                }
            }

            return redirect()->route('api.web.order.credit_card_checkout', ['id' => $id, 'unique_id' => $unique_id]);
        }

        $order = DB::table('ord_orders as order')
            ->leftJoin('usr_customers as customer', 'order.email', '=', 'customer.email')
            ->leftJoin('prd_sale_channels as sale', 'sale.id', '=', 'order.sale_channel_id')
            ->leftJoin('ord_received_orders as received', 'received.order_id', '=', 'order.id')
            ->join('ord_payment_credit_card_log as cc_log', 'cc_log.order_id', '=', 'order.id')
            ->select([
                'order.id',
                'order.sn',
                'order.discount_value',
                'order.discounted_price',
                'order.dlv_fee',
                'order.origin_price',
                'order.note',
                'order.status_code',
                'order.status',
                'order.total_price',
                'order.created_at',
                'order.unique_id',
                'customer.name',
                'customer.email',
                'sale.title as sale_title',
                'received.sn as received_sn',
                'cc_log.status as log_status',
                'cc_log.errdesc as log_errdesc',
                'cc_log.authcode as log_authcode',
                'cc_log.authamt as log_authamt',
                'cc_log.cardnumber as log_cardnumber',
                'cc_log.created_at as log_created_at',
            ])
            ->where([
                'order.id' => $id,
                'order.unique_id' => $unique_id,
                'received.deleted_at' => null,
            ])
            ->where(function ($q) use ($EncArray) {
                if (count($EncArray) > 0) {
                    $q->where([
                        'cc_log.status' => $EncArray['status'],
                    ]);
                }
            })
            ->get()
            ->last();

        if (!$order) {
            return abort(404);
        }

        return view('cms.frontend.checkout_result', [
            'order' => $order,
        ]);
    }

    public function createOrder(Request $request)
    {

        $payLoad = request()->getContent();

        if (!$payLoad) {
            return response()->json([
                'status' => 'E01',
                'message' => '參數不能為空值',
            ]);
        }

        $payLoad = json_decode($payLoad, true);

        $valiRule = [
            "orderer.name" => "required",
            "orderer.phone" => "required",
            "orderer.region_id" => "required|numeric",
            "orderer.addr" => "required",
            "recipient.name" => "required",
            "recipient.phone" => "required",
            "recipient.region_id" => "required|numeric",
            "recipient.addr" => "required",
            "payment" => Rule::in([ReceivedMethod::Cash()->value, ReceivedMethod::CreditCard()->value]),
            "products" => 'array|required',
            "products.*.qty" => "required|numeric",
            "products.*.product_id" => "required",
            "products.*.product_style_id" => "required",
            "products.*.shipment_type" => "required",
            "products.*.shipment_event_id" => "required",
        ];

        if (!Auth::guard('sanctum')->check()) {
            $valiRule['email'] = 'required|email';
        }

        $validator = Validator::make($payLoad, $valiRule);

        if ($validator->fails()) {
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
            }
        } else {
            $customer = $request->user();
        }

        $address = [];
        $address[] = ['name' => $payLoad['orderer']['name'],
            'phone' => $payLoad['orderer']['phone'],
            'address' => Addr::fullAddr($payLoad['orderer']['region_id'], $payLoad['orderer']['addr']),
            'type' => UserAddrType::orderer()->value];

        $address[] = ['name' => $payLoad['orderer']['name'],
            'phone' => $payLoad['orderer']['phone'],
            'address' => Addr::fullAddr($payLoad['orderer']['region_id'], $payLoad['orderer']['addr']),
            'type' => UserAddrType::sender()->value];

        $address[] = ['name' => $payLoad['recipient']['name'],
            'phone' => $payLoad['recipient']['phone'],
            'address' => Addr::fullAddr($payLoad['recipient']['region_id'], $payLoad['recipient']['addr']),
            'type' => UserAddrType::receiver()->value];

        $couponObj = null;
        if (isset($payLoad['coupon_type']) && isset($payLoad['coupon_sn'])) {
            $couponObj = [$payLoad['coupon_type'], $payLoad['coupon_sn']];
        }

        $dividend = [];
        if (isset($payLoad['points']) && isset($payLoad['points'])) {
            $dividend = $payLoad['points'];
        }

        $re = Order::createOrder($customer->email, 1, $address, $payLoad['products'], null, $couponObj, ReceivedMethod::fromValue($payLoad['payment']), $dividend);

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

        $order->sub_order = array_map(function ($n) {
            $delivery = Delivery::getDeliveryWithEventWithSn(Event::order()->value, $n->id)->get()->first();
            $n->shipment_flow = LogisticFlow::getListByDeliveryId($delivery->id)->select('status', 'created_at')->get()->toArray();

            $n->items = json_decode($n->items);
            foreach ($n->items as $key => $value) {
                if ($value->img_url) {
                    $n->items[$key]->img_url = asset($n->items[$key]->img_url);
                } else {
                    $n->items[$key]->img_url = '';
                }
            }
            return $n;
        }, $subOrder);

        // credit card start
        include app_path() . '/Helpers/auth_mpi_mac.php';

        $str_mer_id = '77725';
        $str_merchant_id = '8220300000043';
        $str_terminal_id = '90300043';

        $str_url = 'https://testepos.ctbcbank.com/mauth/SSLAuthUI.jsp';

        $arr_data = [
            'MerchantID' => $str_merchant_id,
            'TerminalID' => $str_terminal_id,
            'lidm' => $order->sn,
            'purchAmt' => $order->total_price,
            'txType' => '0',
            'Option' => 0,
            'Key' => 'LPCvSznVxZ4CFjnWbtg4mUWo',
            'MerchantName' => mb_convert_encoding($order->sale_title, 'BIG5', ['BIG5', 'UTF-8']),
            'AuthResURL' => route('api.web.order.credit_card_checkout_api', ['id' => $order->id]),
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
        // credit card end

        $re = [];
        $re[ResponseParam::status()->key] = '0';
        $re[ResponseParam::data()->key] = $order;

        return response()->json($re);
    }

    /**
     * frontend credit card checkout result
     *
     * @param  Request  $request
     * @param  int  $id primary ID of ord_orders
     *
     * @return reidrect
     */
    public function credit_card_checkout_api(Request $request, $id)
    {
        include app_path() . '/Helpers/auth_mpi_mac.php';

        $EncRes = request('URLResEnc') ? request('URLResEnc') : null;
        if ($EncRes) {
            $Key = 'LPCvSznVxZ4CFjnWbtg4mUWo';
            $debug = '0';
            $EncArray = gendecrypt($EncRes, $Key, $debug);

            if (is_array($EncArray) && count($EncArray) > 0) {
                $status = isset($EncArray['status']) ? $EncArray['status'] : '';
                $authAmt = isset($EncArray['authamt']) ? $EncArray['authamt'] : '';
                $lidm = isset($EncArray['lidm']) ? $EncArray['lidm'] : '';

                if (empty($status) && $status == '0') {
                    // echo '交易完成';
                    OrderPayCreditCard::create_log($id, (object) $EncArray);

                    $received_order_collection = ReceivedOrder::where([
                        'order_id' => $id,
                        'deleted_at' => null,
                    ]);

                    if (!$received_order_collection->first()) {
                        $received_order = ReceivedOrder::create_received_order($id);
                        $received_method = ReceivedMethod::CreditCard; // 'credit_card'

                        $data = [];
                        $data['acc_transact_type_fk'] = $received_method;
                        $data[$received_method]['installment'] = 'none';
                        $result_id = ReceivedOrder::store_received_method($data);

                        $parm = [];
                        $parm['received_order_id'] = $received_order->id;
                        $parm['received_method'] = $received_method;
                        $parm['received_method_id'] = $result_id;
                        $parm['grade_id'] = ReceivedDefault::where('name', $received_method)->first() ? ReceivedDefault::where('name', $received_method)->first()->default_grade_id : 0;
                        $parm['price'] = $authAmt;
                        ReceivedOrder::store_received($parm);
                    }

                    return redirect(env('FRONTEND_URL') . 'payfin/' . $id . '/' . $lidm . '/' . $status);
                }

                OrderPayCreditCard::create_log($id, (object) $EncArray);
            }
        }

        // echo '交易失敗';
        return redirect(env('FRONTEND_URL') . 'payfin/' . $id . '/' . $lidm . '/1');
    }
}
