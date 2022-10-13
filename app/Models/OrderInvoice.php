<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\DB;

class OrderInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ord_order_invoice';
    protected $guarded = [];


    public static function api_send(string $action, $data)
    {
        if(env('APP_ENV') == 'local' || env('APP_ENV') == 'dev'){
            $valid = [
                'key'=>'ib4VvhGsGqlmH2uE4OWARw1YjKK4l4il',
                'iv'=>'CUCdlIDbOq2EpTJP',
                'MerchantID'=>'32854745',
            ];

            $data['url'] = [
                'invoice_issue' => 'https://cinv.ezpay.com.tw/Api/invoice_issue',
                'invoice_touch_issue' => 'https://cinv.ezpay.com.tw/Api/invoice_touch_issue',
                'invoice_invalid' => 'https://cinv.ezpay.com.tw/Api/invoice_invalid',
                'allowance_issue' => 'https://cinv.ezpay.com.tw/Api/allowance_issue',
                'allowance_touch_issue' => 'https://cinv.ezpay.com.tw/Api/allowance_touch_issue',
                'allowanceInvalid' => 'https://cinv.ezpay.com.tw/Api/allowanceInvalid',
                'invoice_search' => 'https://cinv.ezpay.com.tw/Api/invoice_search',
            ];

        } else {
            // formal env
            $valid = [
                'key'=>'FcgVCBhjBKm0yC5L0m4LiKOWxnaCGk9w',
                'iv'=>'PhLZ8W7nbcBF7iSC',
                'MerchantID'=>'316943976',
            ];

            $data['url'] = [
                'invoice_issue' => 'https://inv.ezpay.com.tw/Api/invoice_issue',
                'invoice_touch_issue' => 'https://inv.ezpay.com.tw/Api/invoice_touch_issue',
                'invoice_invalid' => 'https://inv.ezpay.com.tw/Api/invoice_invalid',
                'allowance_issue' => 'https://inv.ezpay.com.tw/Api/allowance_issue',
                'allowance_touch_issue' => 'https://inv.ezpay.com.tw/Api/allowance_touch_issue',
                'allowanceInvalid' => 'https://inv.ezpay.com.tw/Api/allowanceInvalid',
                'invoice_search' => 'https://inv.ezpay.com.tw/Api/invoice_search',
            ];
        }


        include app_path() . '/Helpers/encode.php';

        $url = $data['url'][$action];

        $result = invoice_encode($data, $url, $valid);

        return $result;
    }


    public static function create_invoice($source_type, $source_id, $parm = [])
    {
        $merge_source = null;

        $item_name_arr = [];
        $item_count_arr = [];
        $item_unit_arr = [];
        $item_price_arr = [];
        $item_amt_arr = [];
        $item_tax_type_arr = [];
        $amt_sales = 0;
        $amt_free = 0;
        $amt = 0;
        if($source_type == app(Order::class)->getTable()){
            $source = Order::orderDetail($source_id)->selectRaw('customer.id AS customer_id')->first();

            $source_id_arr[] = $source_id;

            if(isset($parm['merge_source']) && is_array($parm['merge_source'])){
                $merge_source = implode(',', $parm['merge_source']);
                $source_id_arr = array_unique(array_merge($source_id_arr, $parm['merge_source']));
            }

            /*
                foreach($source_id_arr as $o_id){
                    $n_order = Order::orderDetail($o_id)->first();
                    $n_sub_order = Order::subOrderDetail($o_id)->get();
                    foreach ($n_sub_order as $key => $value) {
                        $n_sub_order[$key]->items = json_decode($value->items);
                        $n_sub_order[$key]->consume_items = json_decode($value->consume_items);
                    }
                    $n_order_discount = DB::table('ord_discounts')->where([
                        'order_type'=>'main',
                        'order_id'=>$o_id,
                    ])->where('discount_value', '>', 0)->get()->toArray();

                    foreach($n_sub_order as $s_value){
                        foreach($s_value->items as $i_value){
                            $item_name_arr[] = trim(mb_substr(str_replace('|', '｜', $i_value->product_title), 0, 30));
                            $item_count_arr[] = $i_value->qty;
                            $item_unit_arr[] = '-';
                            $item_price_arr[] = $i_value->price;
                            $item_amt_arr[] = $i_value->total_price;
                            $item_tax_type_arr[] = $i_value->product_taxation == 1 ? 1 : 3;

                            if($i_value->product_taxation == 1){
                                $amt_sales += round($i_value->total_price/1.05);
                                $amt += round($i_value->total_price/1.05);
                            } else if($i_value->product_taxation == 0){
                                $amt_free += round($i_value->total_price/1);
                                $amt += round($i_value->total_price/1);
                            }
                        }
                    }
                    if($n_order->dlv_fee > 0){
                        $item_name_arr[] = trim(mb_substr(str_replace('|', '｜', '物流費用'), 0, 30));
                        $item_count_arr[] = 1;
                        $item_unit_arr[] = '-';
                        $item_price_arr[] = $n_order->dlv_fee;
                        $item_amt_arr[] = $n_order->dlv_fee;
                        $item_tax_type_arr[] = $n_order->dlv_taxation == 1 ? 1 : 3;

                        if($n_order->dlv_taxation == 1){
                            $amt_sales += round($n_order->dlv_fee/1.05);
                            $amt += round($n_order->dlv_fee/1.05);
                        } else if($n_order->dlv_taxation == 0){
                            $amt_free += round($n_order->dlv_fee/1);
                            $amt += round($n_order->dlv_fee/1);
                        }
                    }
                    foreach($n_order_discount as $d_value){
                        $item_name_arr[] = trim(mb_substr(str_replace('|', '｜', $d_value->title), 0, 30));
                        $item_count_arr[] = 1;
                        $item_unit_arr[] = '-';
                        $item_price_arr[] = -$d_value->discount_value;
                        $item_amt_arr[] = -$d_value->discount_value;
                        $item_tax_type_arr[] = $d_value->discount_taxation == 1 ? 1 : 3;

                        if($d_value->discount_taxation == 1){
                            $amt_sales -= round($d_value->discount_value/1.05);
                            $amt -= round($d_value->discount_value/1.05);
                        } else if($d_value->discount_taxation == 0){
                            $amt_free -= round($d_value->discount_value/1);
                            $amt -= round($d_value->discount_value/1);
                        }
                    }
                }
            */

            foreach($parm['o_title'] as $key => $value){
                $item_name_arr[] = trim(mb_substr(str_replace('|', '｜', preg_replace('/(\t|\r|\n|\r\n)+/', ' ', $parm['o_title'][$key])), 0, 30));
                $item_count_arr[] = $parm['o_qty'][$key];
                $item_unit_arr[] = '-';
                $item_price_arr[] = $parm['o_price'][$key];
                $item_amt_arr[] = $parm['o_total_price'][$key];
                $item_tax_type_arr[] = $parm['o_taxation'][$key] == 1 ? 1 : 3;

                if($parm['o_taxation'][$key] == 1){
                    $amt_sales += round($parm['o_total_price'][$key]/1.05);
                    $amt += round($parm['o_total_price'][$key]/1.05);
                } else if($parm['o_taxation'][$key] == 0){
                    $amt_free += round($parm['o_total_price'][$key]/1);
                    $amt += round($parm['o_total_price'][$key]/1);
                }
            }
        }


        $invoice_id = null;
        $user_id = auth('user')->user() ? auth('user')->user()->id : null;
        $customer_id = $source ? $source->customer_id : null;
        $merchant_order_no = isset($parm['merchant_order_no']) ? $parm['merchant_order_no'] : $source->sn ;

        $status = isset($parm['status']) ? $parm['status'] : 9;
        $create_status_time = isset($parm['create_status_time']) ? $parm['create_status_time'] : null;
        $category = isset($parm['category']) ? $parm['category'] : 'B2C';
        $buyer_name = isset($parm['buyer_name']) ? trim($parm['buyer_name']) : null;
        $buyer_ubn = isset($parm['buyer_ubn']) ? trim($parm['buyer_ubn']) : null;
        $buyer_address = isset($parm['buyer_address']) ? trim($parm['buyer_address']) : null;
        $buyer_email = isset($parm['buyer_email']) ? $parm['buyer_email'] : null;
        $carrier_type = isset($parm['carrier_type']) ? $parm['carrier_type'] : null;
        $carrier_num = isset($parm['carrier_num']) ? trim($parm['carrier_num']) : null;
        $love_code = isset($parm['love_code']) ? $parm['love_code'] : null;

        $print_flag = $carrier_type != null || $love_code ? 'N' : 'Y';
        $kiosk_print_flag = $carrier_type == 2 && isset($parm['kiosk_print_flag']) && $parm['kiosk_print_flag'] == 1 ? $parm['kiosk_print_flag'] : null;
        if(count(array_unique($item_tax_type_arr)) == 1) {
            if(array_unique($item_tax_type_arr)[0] == 1) {
                $tax_type = 1;
            } else if(array_unique($item_tax_type_arr)[0] == 3) {
                $tax_type = 3;
            } else {
                $tax_type = 9;
            }

        } else {
            $tax_type = 9;
        }

        $tax_rate = in_array($tax_type, [1, 9]) ? 5 : 0;
        $customs_clearance = isset($parm['customs_clearance']) ? $parm['customs_clearance'] : null;

        // $amt_sales = $amt_sales;
        $amt_zero = 0;
        // $amt_free = $amt_free;
        $amt = $tax_type == 9 ? ($amt_sales + $amt_zero + $amt_free) : $amt;
        $tax_amt = array_sum($item_amt_arr) - $amt;
        $total_amt = array_sum($item_amt_arr);

        $item_name = implode('|', $item_name_arr);
        $item_count = implode('|', $item_count_arr);
        $item_unit = implode('|', $item_unit_arr);
        $item_price = implode('|', $item_price_arr);
        $item_amt = implode('|', $item_amt_arr);
        $item_tax_type = implode('|', $item_tax_type_arr);
        $comment = isset($parm['comment']) ? $parm['comment'] : null;

        $merchant_id = null;
        $invoice_trans_no = null;
        $invoice_number = 'E' . str_pad((OrderInvoice::get()->count()) + 1, 9, '0', STR_PAD_LEFT);
        $random_number = null;
        $check_code = null;
        $bar_code = null;
        $qr_code_l = null;
        $qr_code_r = null;

        // if($status == 3){
        //     $create_status_time = date('Y-m-d', strtotime("+ 7 day"));
        // }

        if($category === 'B2B'){
            if($tax_type == 9){
                wToast(__('三聯式發票稅別不可為混合課稅'));
                return $inv_result = null;
            }

            $buyer_name = mb_substr($buyer_name, 0, 60);
            $carrier_type = null;
            $carrier_num = null;
            $love_code = null;
            $print_flag = 'Y';

        } else if($category === 'B2C'){
            $buyer_name = mb_substr($buyer_name, 0, 30);
            $buyer_ubn = null;

            if($print_flag == 'N'){
                if($carrier_type != null && $carrier_type == 0){
                    if(preg_match('/^\/[A-Z0-9+-.]{7}$/', $carrier_num) == 0 || strlen($carrier_num) != 8){
                        wToast(__('手機條碼載具格式錯誤'));
                        return $inv_result = null;
                    }

                } else if($carrier_type == 1){
                    if(preg_match('/^[A-Z]{2}[0-9]{14}$/', $carrier_num) == 0 || strlen($carrier_num) != 16){
                        wToast(__('自然人憑證條碼載具格式錯誤'));
                        return $inv_result = null;
                    }

                } else if($carrier_type == 2){
                    $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";
                    if (preg_match($pattern, $buyer_email) == 0) {
                        wToast(__('會員電子發票載具格式錯誤'));
                        return $inv_result = null;
                    }
                    if (app(Order::class)->getTable() == $source_type) {
                        DB::beginTransaction();
                        try {
                            //判斷最後開立電子發票的email 若與現存的不同 代表使用者再次做編輯 需再更新回ord_orders.carrier_num
                            $order = Order::where('id', '=', $source_id)->first();
                            if ($buyer_email != $order->carrier_num) {
                                Order::where('id', '=', $source_id)->update(['carrier_num' => $buyer_email]);
                            }
                            DB::commit();
                        } catch (\Exception $e) {
                            $inv_result = null;
                            // $e->getMessage();
                            wToast(__('會員電子發票載具更新失敗'));
                            DB::rollback();
                            return $inv_result = null;
                        }
                    }

                    $carrier_num = $buyer_email;
                }

            } else {
                $carrier_type = null;
                $carrier_num = null;
                $love_code = null;
            }

            // if($carrier_type != ''){
            //     $love_code = '';
            //     $print_flag = 'Y';

            // } else {
            //     if($love_code != '' && preg_match('/^[0-9]{3,7}$/', $love_code) !== 1){
            //         echo 'love code not match';

            //         wToast(__('捐贈碼格式錯誤'));
            //         return $inv_result = null;
            //     }
            // }

            // if($carrier_type != '2'){
            //     $kiosk_print_flag = '';
            // }
        }

        DB::beginTransaction();

        try {
            $inv_result = self::create([
                'source_type'=>$source_type,
                'source_id'=>$source_id,
                'merge_source_id'=>$merge_source,
                'invoice_id'=>$invoice_id,
                'user_id'=>$user_id,
                'customer_id'=>$customer_id,
                'merchant_order_no'=>$merchant_order_no,
                'status'=>$status,
                'create_status_time'=>$create_status_time,
                'category'=>$category,
                'buyer_name'=>$buyer_name,
                'buyer_ubn'=>$buyer_ubn,
                'buyer_address'=>$buyer_address,
                'buyer_email'=>$buyer_email,
                'carrier_type'=>$carrier_type,
                'carrier_num'=>$carrier_num,
                'love_code'=>$love_code,
                'print_flag'=>$print_flag,
                'kiosk_print_flag'=>$kiosk_print_flag,
                'tax_type'=>$tax_type,
                'tax_rate'=>$tax_rate,
                'customs_clearance'=>$customs_clearance,
                'amt'=>$amt,
                'amt_sales'=>$amt_sales,
                'amt_zero'=>$amt_zero,
                'amt_free'=>$amt_free,
                'tax_amt'=>$tax_amt,
                'total_amt'=>$total_amt,
                'item_name'=>$item_name,
                'item_count'=>$item_count,
                'item_unit'=>$item_unit,
                'item_price'=>$item_price,
                'item_amt'=>$item_amt,
                'item_tax_type'=>$item_tax_type,
                'comment'=>$comment,

                'r_status'=>null,
                'r_msg'=>null,
                'r_json'=>null,
                'merchant_id'=>$merchant_id,
                'invoice_trans_no'=>$invoice_trans_no,
                'invoice_number'=>$invoice_number,
                'random_number'=>$random_number,
                'check_code'=>$check_code,
                'bar_code'=>$bar_code,
                'qr_code_l'=>$qr_code_l,
                'qr_code_r'=>$qr_code_r,
            ]);

            if($merge_source && $inv_result){
                self::where('source_type', $source_type)->whereIn('source_id', $parm['merge_source'])->update([
                    'invoice_id'=>$inv_result->id,
                ]);
            }

            wToast(__('發票資料新增成功'));
            // if($status == 1 && $inv_result){
            //     $inv_result = self::invoice_issue_api($inv_result->id);
            //     wToast(__($inv_result->r_msg));
            // }

            DB::commit();

        } catch (\Exception $e) {
            $inv_result = null;
            // $e->getMessage();
            wToast(__('發票資料新增失敗', ['type'=>'danger']));
            DB::rollback();
        }

        return $inv_result;
    }


    public static function invoice_issue_api($id)
    {
        $target = self::find($id);

        $data = [
            'RespondType' => 'JSON',
            'Version' => '1.5',
            'TimeStamp' => time(),
            'TransNum' => '',
            'MerchantOrderNo' => $target->merchant_order_no,
            'Status' => $target->status,
            'CreateStatusTime' => $target->create_status_time,
            'Category' => $target->category,
            'BuyerName' => $target->buyer_name,
            'BuyerUBN' => $target->buyer_ubn,
            'BuyerAddress' => $target->buyer_address,
            'BuyerEmail' => $target->buyer_email,
            'CarrierType' => $target->carrier_type,
            'CarrierNum' => rawurlencode($target->carrier_num),
            'LoveCode' => $target->love_code,
            'PrintFlag' => $target->print_flag,
            'KioskPrintFlag' => $target->kiosk_print_flag,
            'TaxType' => $target->tax_type,
            'TaxRate' => $target->tax_rate + 0,
            'CustomsClearance' => $target->customs_clearance,
            'Amt' => $target->amt + 0,
            'AmtSales' => $target->amt_sales + 0,
            'AmtZero' => $target->amt_zero + 0,
            'AmtFree' => $target->amt_free + 0,
            'TaxAmt' => $target->tax_amt + 0,
            'TotalAmt' => $target->total_amt + 0,
            'ItemName' => $target->item_name,
            'ItemCount' => $target->item_count,
            'ItemUnit' => $target->item_unit,
            'ItemPrice' => $target->item_price,
            'ItemAmt' => $target->item_amt,
            'ItemTaxType' => $target->item_tax_type,
            'Comment' => $target->comment,
        ];
        $api_result = self::api_send('invoice_issue', $data);

        if($api_result){
            foreach($api_result as $api_key => $api_value){
                if($api_key == 'web_info'){
                    if(is_string(json_decode($api_value)->Result)){
                        $target->update([
                            'r_status'=>json_decode($api_value)->Status,
                            'r_msg'=>mb_convert_encoding(trim(json_decode($api_value)->Message), 'UTF-8', ['BIG5', 'UTF-8']),
                            'r_json'=>json_decode($api_value)->Result,
                            'merchant_id'=>json_decode(json_decode($api_value)->Result)->MerchantID,
                            'invoice_trans_no'=>json_decode(json_decode($api_value)->Result)->InvoiceTransNo,
                            'invoice_number'=>json_decode(json_decode($api_value)->Result)->InvoiceNumber,
                            'random_number'=>json_decode(json_decode($api_value)->Result)->RandomNum,
                            'check_code'=>json_decode(json_decode($api_value)->Result)->CheckCode,
                            'bar_code'=>$target->print_flag == 'Y' ? json_decode(json_decode($api_value)->Result)->BarCode : null,
                            'qr_code_l'=>$target->print_flag == 'Y' ? json_decode(json_decode($api_value)->Result)->QRcodeL : null,
                            'qr_code_r'=>$target->print_flag == 'Y' ? json_decode(json_decode($api_value)->Result)->QRcodeR : null,
                            'updated_at'=>date('Y-m-d H:i:s'),
                        ]);

                    } else {
                        $target->update([
                            'r_status'=>json_decode($api_value)->Status,
                            'r_msg'=>mb_convert_encoding(trim(json_decode($api_value)->Message), 'UTF-8', ['BIG5', 'UTF-8']),
                            'r_json'=>json_decode($api_value)->Result,
                            'updated_at'=>date('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }
        }

        return $target;
    }


    public static function invoice_invalid_api($id)
    {
        // $target = self::find($id);

        // $data = [
        //     'RespondType' => 'JSON',
        //     'Version' => '1.0',
        //     'TimeStamp' => time(),
        //     'InvoiceNumber' => $target->invoice_number,
        //     'InvalidReason' => $target->invalid_reason,
        // ];

        // $api_result = self::api_send('invoice_invalid', $data);
    }


    public static function getData($param) {
        $query = DB::table(app(OrderInvoice::class)->getTable(). ' as ord_invoice')
            ->select('ord_invoice.*'
                , DB::raw('(substring_index(ord_invoice.item_name, "|", 1)) as item_1_name')
                , DB::raw('DATE_FORMAT((ifnull(ord_invoice.create_status_time, ord_invoice.created_at)),"%Y-%m-%d") as invoice_date')
            )
            ->whereNull('ord_invoice.deleted_at');
        if (isset($param['invoice_number'])) {
            $query->where('ord_invoice.invoice_number', '=', $param['invoice_number']);
        }
        if (isset($param['buyer_name'])) {
            $query->where('ord_invoice.buyer_name', '=', $param['buyer_name']);
        }
        if (isset($param['buyer_ubn'])) {
            $query->where('ord_invoice.buyer_ubn', '=', $param['buyer_ubn']);
        }
        if (isset($param['invoice_sdate']) && isset($param['invoice_edate'])) {
            $s_invoice_date = date('Y-m-d', strtotime($param['invoice_sdate']));
            $e_invoice_date = date('Y-m-d', strtotime($param['invoice_edate'] . ' +1 day'));
            $query->whereBetween(DB::raw('ifnull(ord_invoice.create_status_time, ord_invoice.created_at)'), [$s_invoice_date, $e_invoice_date]);
        }
        return $query;
    }
}
