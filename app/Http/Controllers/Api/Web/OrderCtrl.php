<?php

namespace App\Http\Controllers\Api\Web;

use App\Enums\Delivery\Event;
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
use App\Models\GeneralLedger;
use App\Models\LogisticFlow;
use App\Models\Order;
use App\Models\OrderPayCreditCard;
use App\Models\OrderPayLinePay;
use App\Models\OrderRemit;
use App\Models\ReceivedDefault;
use App\Models\ReceivedOrder;
use App\Models\OrderCreateLog;
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
            ->leftJoin('ord_received_orders as received', function ($join) {
                $join->on('received.source_id', '=', 'order.id');
                $join->where([
                    'received.source_type' => app(Order::class)->getTable(),
                    'received.balance_date' => null,
                    'received.deleted_at' => null,
                ]);
            })
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
            ])
            ->where(function ($q) {
                $q->whereRaw('(order.status_code IN ("add"))');
            })
            ->first();

        if (!$order) {
            return abort(404);
        }

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

        $source_type = app(Order::class)->getTable();
        $received_order_collection = ReceivedOrder::where([
            'source_type' => $source_type,
            'source_id' => $id,
        ]);
        $log = OrderPayCreditCard::where([
            'source_type' => $source_type,
            'source_id' => $id,
            'status' => 0,
        ])->orderBy('created_at', 'DESC')->first();
        if ($received_order_collection->first() && !$log) {
            return abort(404);
        }

        $order = Order::orderDetail($id)
            ->leftJoin('ord_payment_credit_card_log as cc_log', function ($join) use ($source_type) {
                $join->on('cc_log.source_id', '=', 'order.id');
                $join->where([
                    'cc_log.source_type' => $source_type,
                ]);
            })
            ->addSelect([
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
            ])
            ->orderBy('cc_log.created_at', 'desc')
            ->first();

        if ($request->isMethod('post')) {
            // avoid f5 reload
            if ($log) {
                return redirect()->route('api.web.order.credit_card_checkout', ['id' => $id, 'unique_id' => $unique_id]);
            }

            include app_path() . '/Helpers/auth_mpi_mac.php';

            $EncRes = request('URLResEnc') ? request('URLResEnc') : null;
            if ($EncRes) {
                $debug = '0';
                $EncArray = gendecrypt($EncRes, $auth_key, $debug);

                if (is_array($EncArray) && count($EncArray) > 0) {
                    $status = isset($EncArray['status']) ? $EncArray['status'] : '';
                    $authAmt = isset($EncArray['authamt']) ? $EncArray['authamt'] : '';
                    $CardNumber = isset($EncArray['cardnumber']) ? $EncArray['cardnumber'] : '';
                    $authCode = isset($EncArray['authcode']) ? $EncArray['authcode'] : '';
                    $EncArray['more_info'] = [];

                    if (empty($status) && $status == '0') {
                        if (!$received_order_collection->first()) {
                            $received_order = ReceivedOrder::create_received_order($source_type, $id);
                            $received_method = ReceivedMethod::CreditCard; //'credit_card'
                            $grade_id = ReceivedDefault::where('name', $received_method)->first() ? ReceivedDefault::where('name', $received_method)->first()->default_grade_id : 0;

                            $data = [];
                            $data['acc_transact_type_fk'] = $received_method;
                            $data[$received_method] = [
                                'cardnumber' => $CardNumber,
                                'authamt' => $authAmt ?? 0,
                                'checkout_date' => date('Y-m-d H:i:s'),
                                'card_type_code' => null,
                                'card_type' => null,
                                'card_owner_name' => $order ? '訂購人' . $order->ord_name : null,
                                'authcode' => $authCode,
                                'all_grades_id' => $grade_id,
                                'checkout_area_code' => 'taipei',
                                'checkout_area' => '台北',
                                'installment' => 'none',
                                'status_code' => 0,
                                'card_nat' => 'local',
                                'checkout_mode' => 'online',
                            ];
                            $result_id = ReceivedOrder::store_received_method($data);

                            $EncArray['more_info'] = $data[$received_method];

                            $parm = [];
                            $parm['received_order_id'] = $received_order->id;
                            $parm['received_method'] = $received_method;
                            $parm['received_method_id'] = $result_id;
                            $parm['grade_id'] = $grade_id;
                            $parm['price'] = $authAmt;
                            ReceivedOrder::store_received($parm);
                        }
                    }

                    OrderPayCreditCard::create_log($source_type, $id, (object) $EncArray);
                }
            }

            return redirect()->route('api.web.order.credit_card_checkout', ['id' => $id, 'unique_id' => $unique_id]);
        }

        // if payment method of credit card has multiple record it can not know which one need to show, so can not left-join acc_received table and acc_received_credit table
        // $order = DB::table('ord_orders as order')
        //     ->leftJoin('usr_customers as customer', 'order.email', '=', 'customer.email')
        //     ->leftJoin('prd_sale_channels as sale', 'sale.id', '=', 'order.sale_channel_id')
        //     ->leftJoin('ord_received_orders as received', function ($join) use ($source_type) {
        //         $join->on('received.source_id', '=', 'order.id');
        //         $join->where([
        //             'received.source_type'=>$source_type,
        //             // 'received.balance_date' => null,
        //             'received.deleted_at' => null,
        //         ]);
        //     })
        //     ->join('ord_payment_credit_card_log as cc_log', function ($join) use ($source_type) {
        //         $join->on('cc_log.source_id', '=', 'order.id');
        //         $join->where([
        //             'cc_log.source_type'=>$source_type,
        //         ]);
        //     })
        //     ->select([
        //         'order.id',
        //         'order.sn',
        //         'order.discount_value',
        //         'order.discounted_price',
        //         'order.dlv_fee',
        //         'order.origin_price',
        //         'order.note',
        //         'order.status_code',
        //         'order.status',
        //         'order.total_price',
        //         'order.created_at',
        //         'order.unique_id',
        //         'customer.name',
        //         'customer.email',
        //         'sale.title as sale_title',
        //         'received.sn as received_sn',
        //         'cc_log.status as log_status',
        //         'cc_log.errdesc as log_errdesc',
        //         'cc_log.authcode as log_authcode',
        //         'cc_log.authamt as log_authamt',
        //         'cc_log.cardnumber as log_cardnumber',
        //         'cc_log.created_at as log_created_at',
        //     ])
        //     ->where([
        //         'order.id' => $id,
        //         'order.unique_id' => $unique_id,
        //     ])
        //     ->where(function ($q) use ($EncArray) {
        //         if (count($EncArray) > 0) {
        //             $q->where([
        //                 'cc_log.status' => $EncArray['status'],
        //             ]);
        //         }
        //     })
        //     ->get()
        //     ->last();

        // if (!$order) {
        //     return abort(404);
        // }

        // $received_order_data = $received_order_collection->get();
        // $received_data = ReceivedOrder::get_received_detail($received_order_data->pluck('id')->toArray());

        if (!$order || !$order->log_created_at) {
            return abort(404);
        }

        $received_order_data = $received_order_collection->get();
        $received_data = ReceivedOrder::get_received_detail($received_order_data->pluck('id')->toArray());

        return view('cms.frontend.checkout_result', [
            'order' => $order,
            'received_data' => count($received_data) > 0 ? $received_data[0] : null,
        ]);
    }

    public function line_pay_payment(Request $request, $source_type, $source_id, $unique_id)
    {
        $request->merge([
            'id' => $source_id,
            'unique_id' => $unique_id,
        ]);

        $sn = 'none';
        $query_arr = [];

        if($source_type == app(Order::class)->getTable()){
            $request->validate([
                'id' => 'required|exists:ord_orders,id',
                'unique_id' => 'required|exists:ord_orders,unique_id',
            ]);

            $order = Order::orderDetail($source_id)->where([
                'order.unique_id' => $unique_id,
                'order.status' => '建立',
            ])->get()->first();
            if(!$order){
                return abort(404);
            }

            $subOrder = Order::subOrderDetail($source_id)->get()->toArray();

            $company_name = DB::table('acc_company')->first()->company;
            $sn = $order->sn;
            $query_arr[] = 'em=' . base64_encode(trim($order->email));

            $packages = [];
            if (count($subOrder) > 0) {
                foreach ($subOrder as $key => $value) {
                    $subOrder[$key]->items = json_decode($value->items);
                    $products = [];

                    foreach($value->items as $i_value) {
                        $products[] = [
                            'name' => $i_value->product_title,
                            'imageUrl' => getImageUrl($i_value->img_url, true),
                            'quantity' => $i_value->qty,
                            'price' => $i_value->price
                        ];
                    }

                    if($value->discount_value > 0){
                        $products[] = [
                            'name' => '購物金折抵或其它活動折扣',
                            'quantity' => 1,
                            'price' => -$value->discount_value
                        ];
                    }

                    $packages[] = [
                        'id' => $value->sn,
                        // 'amount' => $value->total_price,
                        'amount' => $value->discounted_price,
                        'name' => $company_name,
                        'products' => $products
                    ];
                }
            }
            if($order->dlv_fee > 0){
                $packages[] = [
                    'id' => 'L' . date('Ymd') . uniqid('-'),
                    'amount' => $order->dlv_fee,
                    'name' => $company_name,
                    'products' => [
                        [
                            'name' => '物流費用',
                            'quantity' => 1,
                            'price' => $order->dlv_fee
                        ]
                    ]
                ];
            }
            // if($order->discount_value > 0){
            //     $order_discount = DB::table('ord_discounts')->where([
            //         'order_type'=>'main',
            //         'order_id'=>$source_id,
            //     ])->where('discount_value', '>', 0)->get()->toArray();

            //     $products = [];

            //     foreach($order_discount as $d_value){
            //         $products[] = [
            //             'name' => $d_value->title,
            //             'quantity' => 1,
            //             'price' => -$d_value->discount_value
            //         ];
            //     }

            //     $packages[] = [
            //         'id' => 'D' . date('Ymd') . uniqid('-'),
            //         'amount' => -$order->discount_value,
            //         'name' => $company_name,
            //         'products' => $products
            //     ];
            // }
            $query_str = count($query_arr) > 0 ? '?' . implode('&', $query_arr) : '';
            $data = [
                'amount' => $order->total_price,
                'currency' => 'TWD',
                'orderId' => $order->sn,
                'packages' => $packages,
                'redirectUrls' => [
                    'confirmUrl' => route('api.web.order.line-pay-confirm', ['source_type'=>app(Order::class)->getTable(), 'source_id'=>$order->id, 'unique_id'=>$order->unique_id]),
                    'cancelUrl' => env('FRONTEND_URL') . 'payfin/' . $source_id . '/' . $sn . '/1' . $query_str,
                ]
            ];

            $result = OrderPayLinePay::api_send('request', null, $data);
            $result->more_info = ['action'=>'request'];
            OrderPayLinePay::create_log($source_type, $source_id, $result);

            if($result->returnCode == '0000'){
                if($request->server('HTTP_USER_AGENT')){
                    $check_mobile = preg_match('/(Mobile|Android|Tablet|GoBrowser|[0-9]x[0-9]*|uZardWeb\/|Mini|Doris\/|Skyfire\/|iPhone|Fennec\/|Maemo|Iris\/|CLDC\-|Mobi\/)/uis', $request->server('HTTP_USER_AGENT'));
                    if($check_mobile){
                        // mobile
                        // return redirect($result->info->paymentUrl->app);
                        return redirect($result->info->paymentUrl->web);
                    } else {
                        // desktop
                        return redirect($result->info->paymentUrl->web);
                    }
                }

            } else {
                $query_arr[] = 'err_msg=' . __($result->returnMessage);
            }
        }

        // echo '交易失敗';
        $query_str = count($query_arr) > 0 ? '?' . implode('&', $query_arr) : '';
        return redirect(env('FRONTEND_URL') . 'payfin/' . $source_id . '/' . $sn . '/1' . $query_str);
    }

    public function line_pay_confirm(Request $request, $source_type, $source_id, $unique_id)
    {
        $request->merge([
            'id' => $source_id,
            'unique_id' => $unique_id,
            'sn' => request('orderId'),
            'transaction_id' => request('transactionId'),
        ]);

        $sn = request('orderId');
        $query_arr = [];

        if($source_type == app(Order::class)->getTable()){
            $request->validate([
                'id' => 'required|exists:ord_orders,id',
                'unique_id' => 'required|exists:ord_orders,unique_id',
                'sn' => 'required|exists:ord_orders,sn',
                'transaction_id' => 'required|exists:ord_payment_line_pay_log,transaction_id',
            ]);

            $order = Order::orderDetail($source_id)->where([
                'order.unique_id' => $unique_id,
                'order.status' => '建立',
            ])->get()->first();
            if(!$order){
                return abort(404);
            }

            $query_arr[] = 'em=' . base64_encode(trim($order->email));

            $data = [
                'amount' => $order->total_price,
                'currency' => 'TWD',
            ];
            $result = OrderPayLinePay::api_send('confirm', request('transactionId'), $data);

            if($result->returnCode == '0000'){
                $received_order = ReceivedOrder::where([
                        'source_type' => $source_type,
                        'source_id' => $source_id,
                    ])->first();

                if (!$received_order) {
                    $received_order = ReceivedOrder::create_received_order($source_type, $source_id);
                    $received_method = ReceivedMethod::AccountsReceivable; //'account_received'
                    $grade = ReceivedDefault::leftJoinSub(GeneralLedger::getAllGrade(), 'grade', function($join) {
                            $join->on('grade.primary_id', 'acc_received_default.default_grade_id');
                        })->where('grade.code', '11050010')->selectRaw('grade.primary_id AS id, grade.code AS code, grade.name AS name')->first();

                    $data = [];
                    $data['acc_transact_type_fk'] = $received_method;
                    $data[$received_method] = [
                        'grade_id' => $grade->id,
                        'grade_code' => $grade->code,
                        'grade_name' => $grade->name,
                        'action' => 'confirm',
                        'checkout_mode' => 'online',
                    ];
                    $result_id = ReceivedOrder::store_received_method($data);

                    $result->more_info = $data[$received_method];

                    $parm = [];
                    $parm['received_order_id'] = $received_order->id;
                    $parm['received_method'] = $received_method;
                    $parm['received_method_id'] = $result_id;
                    $parm['grade_id'] = $grade->id;
                    $parm['price'] = $order->total_price;
                    ReceivedOrder::store_received($parm);

                } else {
                    $result->more_info = [
                        'action' => 'confirm',
                    ];
                }

                OrderPayLinePay::create_log($source_type, $source_id, $result);

                $query_str = count($query_arr) > 0 ? '?' . implode('&', $query_arr) : '';
                return redirect(env('FRONTEND_URL') . 'payfin/' . $source_id . '/' . $sn . '/0' . $query_str);

            } else {
                $result->more_info = [
                    'action' => 'confirm',
                ];

                $query_arr[] = 'err_msg=' . __($result->returnMessage);
            }

            OrderPayLinePay::create_log($source_type, $source_id, $result);
        }

        // echo '交易失敗';
        $query_str = count($query_arr) > 0 ? '?' . implode('&', $query_arr) : '';
        return redirect(env('FRONTEND_URL') . 'payfin/' . $source_id . '/' . $sn . '/1' . $query_str);
    }


    public function createOrder(Request $request)
    {

        $payLoad = request()->getContent();

        if (!$payLoad) {
            OrderCreateLog::create([
                'email'=>'',
                'payload'=>'',
                'return_value'=>'參數不能為空值',
                'success'=> 0
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
            'carrier_num' => 'required_if:carrier_type,==,0|required_if:carrier_type,==,1',

            "orderer.name" => "required",
            "orderer.phone" => "required",
            "orderer.region_id" => "required|numeric",
            "orderer.addr" => "required",
            "recipient.name" => "required",
            "recipient.phone" => "required",
            "recipient.region_id" => "required|numeric",
            "recipient.addr" => "required",
            "payment" => Rule::in([ReceivedMethod::Cash()->value, ReceivedMethod::CreditCard()->value, ReceivedMethod::Remittance()->value, 'line_pay']),
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
            OrderCreateLog::create([
                'email'=>'',
                'payload'=>json_encode($payLoad),
                'return_value'=>json_encode($validator->errors()),
                'success'=> 0
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
            'city_id'=>$payLoad['orderer']['city_id'],
            'region_id'=>$payLoad['orderer']['region_id'],
            'type' => UserAddrType::orderer()->value];

        $address[] = ['name' => $payLoad['orderer']['name'],
            'phone' => $payLoad['orderer']['phone'],
            'address' => $payLoad['orderer']['addr'],
            'city_id'=> $payLoad['orderer']['city_id'],
            'region_id'=> $payLoad['orderer']['region_id'],
            'type' => UserAddrType::sender()->value];

        $address[] = ['name' => $payLoad['recipient']['name'],
            'phone' => $payLoad['recipient']['phone'],
            'address' => $payLoad['recipient']['addr'],
            'city_id'=> $payLoad['recipient']['city_id'],
            'region_id'=> $payLoad['recipient']['region_id'],
            'type' => UserAddrType::receiver()->value];

        $couponObj = null;
        if (isset($payLoad['coupon_type']) && isset($payLoad['coupon_sn'])) {
            $couponObj = [$payLoad['coupon_type'], $payLoad['coupon_sn']];
        }

        $payinfo = null;
        $payinfo['category'] = $payLoad['category'] ?? null;
        $payinfo['invoice_method'] = $d['invoice_method'] ?? InvoiceMethod::e_inv()->key; // 前端官網無紙本發票 所以一律為電子發票
        $payinfo['inv_title'] = $payLoad['inv_title'] ?? null;
        $payinfo['buyer_ubn'] = $payLoad['buyer_ubn'] ?? null;
        $payinfo['love_code'] = $payLoad['love_code'] ?? null;
        $payinfo['carrier_type'] = $payLoad['carrier_type'] ?? null;
        $payinfo['carrier_email'] = $payLoad['carrier_email'] ?? null;
        // 若為會員載具 前端官網不開放編輯修改功能 所以一律為預設email
        if (2 == $payinfo['carrier_type']) {
            $payinfo['carrier_email'] = $customer->email;
        }
        $payinfo['carrier_num'] = $payLoad['carrier_num'] ?? null;

        $dividend = [];
        if (isset($payLoad['points']) && isset($payLoad['points'])) {
            $dividend = $payLoad['points'];
        }

        if($payLoad['payment'] == 'line_pay'){
            $payment = (object) [
                'value' => 'line_pay',
                'description' => 'Line Pay',
            ];

        } else {
            $payment = ReceivedMethod::fromValue($payLoad['payment']);
        }
        $re = Order::createOrder($customer->email, 1, $address, $payLoad['products'], $payLoad['mcode'] ?? null, $payLoad['note'], $couponObj, $payinfo, $payment, $dividend, $request->user());

        if ($re['success'] == '1') {
            DB::commit();
            // log
            OrderCreateLog::create([
                'email'=>$customer->email,
                'payload'=>json_encode($payLoad),
                'return_value'=>json_encode($re),
                'success'=>1
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
            'email'=>$customer->email,
            'payload'=>json_encode($payLoad),
            'return_value'=>json_encode($re),
            'success'=> 0
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
                'logistic_url' => env('LOGISTIC_URL') . 'guest/order-flow/'

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

        $order->sub_order = array_map(function ($n) {
            $delivery = Delivery::getDeliveryWithEventWithSn(Event::order()->value, $n->id)->get()->first();
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

        $order->logistic_url = env('LOGISTIC_URL') . 'guest/order-flow/';
        // credit card end

        // line pay
        $order->line_pay_url = route('api.web.order.line-pay-payment', ['source_type' => app(Order::class)->getTable(), 'source_id' => $order->id, 'unique_id' => $order->unique_id]);

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
        $query_arr = [];

        $order = Order::orderDetail($id)->first();
        if($order){
            $query_arr[] = 'em=' . base64_encode(trim($order->email));
        }

        if ($EncRes) {
            $debug = '0';
            $EncArray = gendecrypt($EncRes, $auth_key, $debug);

            if (is_array($EncArray) && count($EncArray) > 0) {
                $status = isset($EncArray['status']) ? $EncArray['status'] : '';
                $authAmt = isset($EncArray['authamt']) ? $EncArray['authamt'] : '';
                $CardNumber = isset($EncArray['cardnumber']) ? $EncArray['cardnumber'] : '';
                $authCode = isset($EncArray['authcode']) ? $EncArray['authcode'] : '';
                $lidm = isset($EncArray['lidm']) ? $EncArray['lidm'] : '';
                $EncArray['more_info'] = [];

                $source_type = app(Order::class)->getTable();

                if (empty($status) && $status == '0') {
                    // echo '交易完成';
                    $received_order_collection = ReceivedOrder::where([
                        'source_type' => $source_type,
                        'source_id' => $id,
                    ]);

                    if (!$received_order_collection->first()) {
                        $received_order = ReceivedOrder::create_received_order($source_type, $id);
                        $received_method = ReceivedMethod::CreditCard; //'credit_card'
                        $grade_id = ReceivedDefault::where('name', $received_method)->first() ? ReceivedDefault::where('name', $received_method)->first()->default_grade_id : 0;

                        $data = [];
                        $data['acc_transact_type_fk'] = $received_method;
                        $data[$received_method] = [
                            'cardnumber' => $CardNumber,
                            'authamt' => $authAmt ?? 0,
                            'checkout_date' => date('Y-m-d H:i:s'),
                            'card_type_code' => null,
                            'card_type' => null,
                            'card_owner_name' => $order ? '訂購人' . $order->ord_name : null,
                            'authcode' => $authCode,
                            'all_grades_id' => $grade_id,
                            'checkout_area_code' => 'taipei',
                            'checkout_area' => '台北',
                            'installment' => 'none',
                            'status_code' => 0,
                            'card_nat' => 'local',
                            'checkout_mode' => 'online',
                        ];
                        $result_id = ReceivedOrder::store_received_method($data);

                        $EncArray['more_info'] = $data[$received_method];

                        $parm = [];
                        $parm['received_order_id'] = $received_order->id;
                        $parm['received_method'] = $received_method;
                        $parm['received_method_id'] = $result_id;
                        $parm['grade_id'] = $grade_id;
                        $parm['price'] = $authAmt;
                        ReceivedOrder::store_received($parm);
                    }

                    OrderPayCreditCard::create_log($source_type, $id, (object) $EncArray);

                    $query_str = count($query_arr) > 0 ? '?' . implode('&', $query_arr) : '';
                    return redirect(env('FRONTEND_URL') . 'payfin/' . $id . '/' . $lidm . '/' . $status . $query_str);

                } else {
                    if(isset($EncArray['errdesc']) && !is_null($EncArray['errdesc'])){
                        $query_arr[] = 'err_msg=' . __(mb_convert_encoding(trim($EncArray['errdesc'], "\x00..\x08"), 'UTF-8', ['BIG5', 'UTF-8']));
                    }
                }

                OrderPayCreditCard::create_log($source_type, $id, (object) $EncArray);
            }
        }

        // echo '交易失敗';
        $query_str = count($query_arr) > 0 ? '?' . implode('&', $query_arr) : '';
        return redirect(env('FRONTEND_URL') . 'payfin/' . $id . '/' . $lidm . '/1' . $query_str);
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
        Order::cancelOrder($d['order_id'],'frontend');

        return [
            'status' => '0',
        ];

    }
}
