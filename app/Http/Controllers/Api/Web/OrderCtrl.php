<?php

namespace App\Http\Controllers\Api\Web;

use App\Enums\Globals\ResponseParam;
use App\Enums\Order\UserAddrType;
use App\Enums\Received\ReceivedMethod;
use App\Http\Controllers\Controller;
// use App\Enums\;
use App\Models\Addr;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Delivery;
use App\Models\LogisticFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Enums\Delivery\Event;

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
            ->leftJoin('pcs_received_orders as received', 'received.order_id', '=', 'order.id')
            ->select([
                'order.id',
                'order.sn',
                'order.discount_value',
                'order.discounted_price',
                'order.dlv_fee',
                'order.origin_price',
                'order.note',
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
                'received.deleted_at' => null,
            ])
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
            'AuthResURL' => route('api.web.order.credit_card_checkout'),
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

    public function credit_card_checkout(Request $request)
    {
        include app_path() . '/Helpers/auth_mpi_mac.php';

        // $EncRes = isset($_POST['URLResEnc']) ? $_POST['URLResEnc'] : null;
        $EncRes = request('URLResEnc') ? request('URLResEnc') : null;
        if ($EncRes) {
            $Key = 'LPCvSznVxZ4CFjnWbtg4mUWo';
            $debug = '0';
            $EncArray = gendecrypt($EncRes, $Key, $debug);

            if (is_array($EncArray) && count($EncArray) > 0) {
                foreach ($EncArray as $key_name => $value) {
                    echo $key_name . " => " . mb_convert_encoding(trim($value, "\x00..\x08"), 'UTF-8', ['BIG5', 'UTF-8']) . "<br>\n";
                    // echo $key_name . " => " . urlencode(trim($value, "\x00..\x08")) ."<br>\n";
                }

                $MACString = '';
                $status = isset($EncArray['status']) ? $EncArray['status'] : "";
                $errCode = isset($EncArray['errcode']) ? $EncArray['errcode'] : "";
                $authCode = isset($EncArray['authcode']) ? $EncArray['authcode'] : "";
                $authAmt = isset($EncArray['authamt']) ? $EncArray['authamt'] : "";
                $lidm = isset($EncArray['lidm']) ? $EncArray['lidm'] : "";
                $OffsetAmt = isset($EncArray['offsetamt']) ? $EncArray['offsetamt'] : "";
                $OriginalAmt = isset($EncArray['originalamt']) ? $EncArray['originalamt'] : "";
                $UtilizedPoint = isset($EncArray['utilizedpoint']) ? $EncArray['utilizedpoint'] : "";
                $Option = isset($EncArray['numberofpay']) ? $EncArray['numberofpay'] : "";
                //紅利交易時請帶入prodcode
                //$Option = isset($EncArray['prodcode']) ? $EncArray['prodcode'] : "";
                $Last4digitPAN = isset($EncArray['last4digitpan']) ? $EncArray['last4digitpan'] : "";

                $MACString = auth_out_mac($status, $errCode, $authCode, $authAmt, $lidm, $OffsetAmt, $OriginalAmt, $UtilizedPoint, $Option, $Last4digitPAN, $Key, $debug);
                echo "產生伺服器所回傳的資料壓碼(MACString)==> $MACString\n" . '<br>';
                // if ($MACString == $EncArray['outmac']){
                //     // then the result is right!
                // }

                $pidResult = isset($EncArray['pidresult']) ? $EncArray['pidresult'] : "";
                $CardNumber = isset($EncArray['cardnumber']) ? $EncArray['cardnumber'] : "";
                $CardNo = isset($EncArray['cardno']) ? $EncArray['cardno'] : "";
                $EInvoice = isset($EncArray['einvoice']) ? $EncArray['einvoice'] : "";

                if (empty($status) && $status == '0') {
                    echo '交易完成';
                    echo '<br>';
                    echo '<a href="' . route('cms.order.index') . '">回到訂單管理</a>';
                    die();
                    // return redirect()->back();
                }
            }
        }

        echo '交易失敗';
        echo '<br>';
        echo '<a href="' . route('cms.order.index') . '">回到訂單管理</a>';
        // return redirect()->back();
    }

    public function createOrder(Request $request)
    {
        $payLoad = json_decode(request()->getContent(), true);

        $validator = Validator::make($payLoad, [
            'email' => 'required|email',
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

    public function orderDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'order_id' => 'required',
        ]);

        if ($validator->fails()) {
            $re = [];
            $re[ResponseParam::status()->key] = 'E01';
            $re[ResponseParam::msg()->key] = $validator->errors();

            return response()->json($re);
        }
        $d = $request->all();

        $order = Order::orderDetail($d['order_id'], $d['email'])->get()->first();
        if (!$order) {
            $re = [];
            $re[ResponseParam::status()->key] = 'E04';
            $re[ResponseParam::msg()->key] = "查無訂單";

            return response()->json($re);
        }

       

        

        $subOrder = Order::subOrderDetail($d['order_id'])->get()->toArray();
     //  dd($subOrder);
        $order->sub_order = array_map(function ($n) {
            $delivery = Delivery::getDeliveryWithEventWithSn(Event::order()->value, $n->order_id)->get()->first();
            $n->shipment_flow = LogisticFlow::getListByDeliveryId($delivery->id)->select('status','created_at')->get()->toArray();
            
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

    public function credit_card_checkout_api(Request $request, $id)
    {
        include app_path() . '/Helpers/auth_mpi_mac.php';

        // $EncRes = isset($_POST['URLResEnc']) ? $_POST['URLResEnc'] : null;
        $EncRes = request('URLResEnc') ? request('URLResEnc') : null;
        if ($EncRes) {
            $Key = 'LPCvSznVxZ4CFjnWbtg4mUWo';
            $debug = '0';
            $EncArray = gendecrypt($EncRes, $Key, $debug);

            if (is_array($EncArray) && count($EncArray) > 0) {
                $status = isset($EncArray['status']) ? $EncArray['status'] : "";
                $lidm = isset($EncArray['lidm']) ? $EncArray['lidm'] : "";
                // $order = Order::where('sn', $lidm)->first();

                if (empty($status) && $status == '0') {
                    // echo '交易完成';
                    return redirect('https://dev-shopp.bestselection.com.tw/payfin/' . $id . '/' . $lidm . '/' . $status);
                }
            }
        }

        // echo '交易失敗';
        return redirect('https://dev-shopp.bestselection.com.tw/payfin/' . $id . '/' . $lidm . '/1');
    }
}
