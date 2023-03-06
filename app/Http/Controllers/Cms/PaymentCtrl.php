<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use App\Enums\Received\ReceivedMethod;

use App\Models\CrdCreditCard;
use App\Models\GeneralLedger;
use App\Models\Order;
use App\Models\OrderPayCreditCard;
use App\Models\OrderPayLinePay;
use App\Models\ReceivedDefault;
use App\Models\ReceivedOrder;


class PaymentCtrl extends Controller
{
    // frontend result
    public function credit_card_result(Request $request, $id)
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
                        $card_info = CrdCreditCard::get_credit_card_info($CardNumber);

                        $data = [];
                        $data['acc_transact_type_fk'] = $received_method;
                        $data[$received_method] = [
                            'cardnumber' => $CardNumber,
                            'authamt' => $authAmt ?? 0,
                            'checkout_date' => date('Y-m-d H:i:s'),
                            'card_type_code' => $card_info->card_type_code,
                            'card_type' => $card_info->card_type,
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


    // backend pay
    public function credit_card(Request $request, $id, $unique_id)
    {
        $request->merge([
            'id' => $id,
            'unique_id' => $unique_id,
        ]);

        $request->validate([
            'id' => 'required|exists:ord_orders,id',
            // 'unique_id' => 'required|exists:ord_orders,unique_id',
        ]);

        $order = Order::orderDetail($id)
            ->leftJoin('ord_received_orders as received', function ($join) {
                $join->on('received.source_id', '=', 'order.id');
                $join->where([
                    'received.source_type' => app(Order::class)->getTable(),
                    'received.balance_date' => null,
                    'received.deleted_at' => null,
                ]);
            })
            ->addSelect([
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

        $sub_order = Order::subOrderDetail($id)->get();
        foreach ($sub_order as $key => $value) {
            $sub_order[$key]->items = json_decode($value->items);
            $sub_order[$key]->consume_items = json_decode($value->consume_items);
        }

        $order_discount = DB::table('ord_discounts')->where([
            'order_type' => 'main',
            'order_id' => $id,
        ])->where('discount_value', '>', 0)->get()->toArray();

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
            'AuthResURL' => route('payment.credit-card-checkout', ['id' => $id, 'unique_id' => $unique_id]),
            'OrderDetail' => mb_convert_encoding($order->note, 'BIG5', ['BIG5', 'UTF-8']),
            'AutoCap' => '1',
            'Customize' => ' ',
            'debug' => '0',
        ];

        $str_mac_string = auth_in_mac($arr_data['MerchantID'], $arr_data['TerminalID'], $arr_data['lidm'], $arr_data['purchAmt'], $arr_data['txType'], $arr_data['Option'], $arr_data['Key'], $arr_data['MerchantName'], $arr_data['AuthResURL'], $arr_data['OrderDetail'], $arr_data['AutoCap'], $arr_data['Customize'], $arr_data['debug']);

        $str_url_enc = get_auth_urlenc($arr_data['MerchantID'], $arr_data['TerminalID'], $arr_data['lidm'], $arr_data['purchAmt'], $arr_data['txType'], $arr_data['Option'], $arr_data['Key'], $arr_data['MerchantName'], $arr_data['AuthResURL'], $arr_data['OrderDetail'], $arr_data['AutoCap'], $arr_data['Customize'], $str_mac_string, $arr_data['debug']);

        return view('cms.frontend.payment.credit_card.checkout', [
            'order' => $order,
            'sub_order' => $sub_order,
            'order_discount' => $order_discount,
            'str_url' => $str_url,
            'str_mac_string' => $str_mac_string,
            'str_mer_id' => $str_mer_id,
            'str_url_enc' => $str_url_enc,
        ]);
    }


    // backend result
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
                return redirect()->route('payment.credit-card-checkout', ['id' => $id, 'unique_id' => $unique_id]);
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
                            $card_info = CrdCreditCard::get_credit_card_info($CardNumber);

                            $data = [];
                            $data['acc_transact_type_fk'] = $received_method;
                            $data[$received_method] = [
                                'cardnumber' => $CardNumber,
                                'authamt' => $authAmt ?? 0,
                                'checkout_date' => date('Y-m-d H:i:s'),
                                'card_type_code' => $card_info->card_type_code,
                                'card_type' => $card_info->card_type,
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

            return redirect()->route('payment.credit-card-checkout', ['id' => $id, 'unique_id' => $unique_id]);
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

        return view('cms.frontend.payment.credit_card.checkout_result', [
            'order' => $order,
            'received_data' => count($received_data) > 0 ? $received_data[0] : null,
        ]);
    }


    public function line_pay(Request $request, $source_type, $source_id, $unique_id = null)
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
                    'confirmUrl' => route('payment.line-pay-confirm', ['source_type'=>app(Order::class)->getTable(), 'source_id'=>$order->id, 'unique_id'=>$order->unique_id]),
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


    public function line_pay_confirm(Request $request, $source_type, $source_id, $unique_id = null)
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
}
