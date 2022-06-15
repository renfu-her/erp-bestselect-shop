<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\DB;

use App\Enums\Order\OrderStatus;
use App\Enums\Order\PaymentStatus;
use App\Enums\Received\ReceivedMethod;

class ReceivedOrder extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'ord_received_orders';
    protected $guarded = [];


    public static function received_order_list(
        $customer_id = null,
        $r_order_sn = null,
        $order_sn = null,
        $r_order_price = null,
        $r_order_receipt_date = null,
        $received_date = null,
        $check_review = 'all'
    ){
        $received_order = DB::table('ord_received_orders as ro')
            ->leftJoin('ord_orders as order', 'order.id', '=', 'ro.order_id')
            ->leftJoin('usr_customers as customer', 'customer.email', '=', 'order.email')
            ->leftJoin('usr_users as user', 'user.id', '=', 'ro.usr_users_id')
            ->leftJoin(DB::raw('(
                SELECT received_order_id,
                MAX(created_at) AS received_date,
                SUM(tw_price) AS received_price,
                CONCAT(\'[\', GROUP_CONCAT(\'{
                        "received_method":"\', received_method, \'",
                        "received_method_id":"\', COALESCE(received_method_id, ""), \'",
                        "all_grades_id":"\', all_grades_id, \'",
                        "tw_price":"\', tw_price, \'",
                        "accountant_id_fk":"\', accountant_id_fk, \'",
                        "note":"\', COALESCE(note, ""),\'",
                        "created_at":"\', created_at,\'"
                    }\' ORDER BY acc_received.id), \']\') AS list
                FROM acc_received
                GROUP BY received_order_id
                ) AS v_table_1'), function ($join){
                    $join->on('v_table_1.received_order_id', '=', 'ro.id');
            })
            ->leftJoin(DB::raw('(
                SELECT order_id,
                CONCAT(\'[\', GROUP_CONCAT(\'{
                        "sub_order_id":"\', sub_order_id, \'",
                        "product_style_id":"\', product_style_id, \'",
                        "sku":"\', sku, \'",
                        "product_title":"\', product_title, \'",
                        "price":"\', price, \'",
                        "qty":"\', qty, \'",
                        "origin_price":"\', origin_price, \'",
                        "discount_value":"\', discount_value, \'",
                        "discounted_price":"\', discounted_price, \'"
                    }\' ORDER BY ord_items.id), \']\') AS item
                FROM ord_items
                GROUP BY order_id
                ) AS v_table_2'), function ($join){
                    $join->on('v_table_2.order_id', '=', 'order.id');
            })
            ->leftJoin(DB::raw('(
                SELECT order_id,
                CONCAT(\'[\', GROUP_CONCAT(\'{
                        "sub_order_id":"\', COALESCE(sub_order_id, ""), \'",
                        "order_item_id":"\', COALESCE(order_item_id, ""), \'",
                        "discount_grade_id":"\', COALESCE(discount_grade_id, ""), \'",
                        "title":"\', COALESCE(title, ""), \'",
                        "sn":"\', COALESCE(sn, ""), \'",
                        "category_title":"\', category_title, \'",
                        "category_code":"\', category_code, \'",
                        "extra_id":"\', COALESCE(extra_id, ""), \'",
                        "extra_title":"\', COALESCE(extra_title, ""), \'",
                        "discount_value":"\', COALESCE(discount_value, ""), \'"
                    }\' ORDER BY ord_discounts.id), \']\') AS discount_list
                FROM ord_discounts
                WHERE discount_value IS NOT NULL AND order_type = "main"
                GROUP BY order_id
                ) AS v_table_3'), function ($join){
                    $join->on('v_table_3.order_id', '=', 'order.id');
            })

            ->whereNull('ro.deleted_at')
            ->whereColumn([
                ['ro.price', '=', 'v_table_1.received_price'],
            ])

            ->select(
                'ro.sn as ro_sn',
                'ro.price as ro_price',// 收款單金額(應收)
                'ro.logistics_grade_id as ro_logistics_grade_id',
                'ro.product_grade_id as ro_product_grade_id',
                'ro.receipt_date as ro_receipt_date',// 收款單入帳審核日期
                'ro.invoice_number as ro_invoice_number',

                'order.id as order_id',
                'order.sn as order_sn',
                'order.dlv_fee as order_dlv_fee',
                'order.origin_price as order_origin_price',
                'order.total_price as order_total_price',
                'order.discount_value as order_discount_value',
                'order.discounted_price as order_discounted_price',

                'user.name as creator',

                'v_table_1.list as received_list',
                'v_table_1.received_date as received_date',// 收款單完成收款日期
                'v_table_1.received_price as received_price',// 收款單金額(實收)
                'v_table_2.item as order_item',
                'v_table_3.discount_list as order_discount',

                'customer.id as customer_id',
                'customer.name as customer_name',
                'customer.email as customer_email',
            )
            ->selectRaw('DATE_FORMAT(order.created_at, "%Y-%m-%d") as order_date');

        if ($customer_id) {
            if (gettype($customer_id) == 'array') {
                $received_order->whereIn('customer.id', $customer_id);
            } else {
                $received_order->where('customer.id', $customer_id);
            }
        }

        if ($r_order_sn) {
            $received_order->where(function ($query) use ($r_order_sn) {
                $query->where('ro.sn', 'like', "%{$r_order_sn}%");
            });
        }

        if ($order_sn) {
            $received_order->where(function ($query) use ($order_sn) {
                $query->where('order.sn', 'like', "%{$order_sn}%");
            });
        }

        if ($r_order_price) {
            if (gettype($r_order_price) == 'array' && count($r_order_price) == 2) {
                $min_price = $r_order_price[0] ?? null;
                $max_price = $r_order_price[1] ?? null;
                if($min_price){
                    $received_order->where('ro.price', '>=', $min_price);
                }
                if($max_price){
                    $received_order->where('ro.price', '<=', $max_price);
                }
            }
        }

        if ($r_order_receipt_date) {
            $s_receipt_date = $r_order_receipt_date[0] ? date('Y-m-d', strtotime($r_order_receipt_date[0])) : null;
            $e_receipt_date = $r_order_receipt_date[1] ? date('Y-m-d', strtotime($r_order_receipt_date[1] . ' +1 day')) : null;

            if($s_receipt_date){
                $received_order->where('ro.receipt_date', '>=', $s_receipt_date);
            }
            if($e_receipt_date){
                $received_order->where('ro.receipt_date', '<', $e_receipt_date);
            }
        }

        if ($received_date) {
            $s_received_date = $received_date[0] ? date('Y-m-d', strtotime($received_date[0])) : null;
            $e_received_date = $received_date[1] ? date('Y-m-d', strtotime($received_date[1] . ' +1 day')) : null;

            if($s_received_date){
                $received_order->where('v_table_1.received_date', '>=', $s_received_date);
            }
            if($e_received_date){
                $received_order->where('v_table_1.received_date', '<', $e_received_date);
            }
        }

        if ($check_review == 'all') {
            //
        } else if ($check_review == 0) {
            $received_order->whereNull('ro.receipt_date');
        } else if($check_review == 1){
            $received_order->whereNotNull('ro.receipt_date');
        }

        return $received_order;
    }


    public static function create_received_order($order_id)
    {
        $order_data = Order::findOrFail($order_id);
        $logistics_grade_id = ReceivedDefault::where('name', 'logistics')->first() ? ReceivedDefault::where('name', 'logistics')->first()->default_grade_id : 0;
        $product_grade_id = ReceivedDefault::where('name', 'product')->first() ? ReceivedDefault::where('name', 'product')->first()->default_grade_id : 0;

        $re = self::create([
            'order_id'=>$order_id,
            'usr_users_id'=>auth('user')->user() ? auth('user')->user()->id : null,
            'sn'=>'MSG' . date('ymd') . str_pad( count(self::whereDate('created_at', '=', date('Y-m-d'))->withTrashed()->get()) + 1, 4, '0', STR_PAD_LEFT),
            'price'=>$order_data->total_price,
            // 'tw_dollar'=>0,
            // 'rate'=>1,
            'logistics_grade_id'=>$logistics_grade_id,
            'product_grade_id'=>$product_grade_id,
            // 'created_at'=>date("Y-m-d H:i:s"),
        ]);

        if($re){
            OrderFlow::changeOrderStatus($order_id, OrderStatus::Unbalance());
            Order::change_order_payment_status($order_id, PaymentStatus::Unbalance(), null);
        }

        return $re;
    }


    public static function store_received($parm)
    {
        $received_order_id = $parm['received_order_id'];
        $received_method = isset($parm['received_method']) ? $parm['received_method'] : 'cash';
        $received_method_id = isset($parm['received_method_id']) ? $parm['received_method_id'] : null;
        $grade_id = $parm['grade_id'];
        $price = $parm['price'];
        $accountant_id_fk = isset($parm['accountant_id_fk']) ? $parm['accountant_id_fk'] : 0;
        $note = isset($parm['note']) ? $parm['note'] : null;

        DB::table('acc_received')->insert([
            'received_type'=>self::class,
            'received_order_id'=>$received_order_id,
            'received_method'=>$received_method,
            'received_method_id'=>$received_method_id,
            'all_grades_id'=>$grade_id,
            'tw_price'=>$price,
            'review_date'=>null,
            'accountant_id_fk'=>$accountant_id_fk,
            'note'=>$note,
            'created_at'=>date("Y-m-d H:i:s"),
        ]);

        $received_order = self::find($received_order_id);
        $received_list = self::get_received_detail([$received_order_id]);
        if ( count($received_list) > 0 && $received_order->price == $received_list->sum('tw_price')) {
            $received_order->update([
                'balance_date'=>date("Y-m-d H:i:s"),
            ]);

            OrderFlow::changeOrderStatus($received_order->order_id, OrderStatus::Paided());
            // Order::change_order_payment_status($received_order->order_id, PaymentStatus::Received(), ReceivedMethod::fromValue($received_method));

            $r_method_arr = $received_list->pluck('received_method')->toArray();
            $r_method_title_arr = [];
            foreach($r_method_arr as $v){
                array_push($r_method_title_arr, ReceivedMethod::getDescription($v));
            }
            $r_method['value'] = implode(',', $r_method_arr);
            $r_method['description'] = implode(',', $r_method_title_arr);
            Order::change_order_payment_status($received_order->order_id, PaymentStatus::Received(), (object) $r_method);
        }
    }


    public static function store_received_method($request)
    {
        $id = null;

        switch ($request['acc_transact_type_fk']) {
            // case ReceivedMethod::Cash:

            case ReceivedMethod::Cheque:
                $id = DB::table('acc_received_cheque')->insertGetId([
                    'ticket_number'=>$request[$request['acc_transact_type_fk']]['ticket_number'],
                    'due_date'=>$request[$request['acc_transact_type_fk']]['due_date'],
                    'created_at'=>date("Y-m-d H:i:s"),
                ]);
                break;

            case ReceivedMethod::CreditCard:
                $id = DB::table('acc_received_credit')->insertGetId([
                    'cardnumber'=>$request[$request['acc_transact_type_fk']]['cardnumber'],
                    'authamt'=>$request[$request['acc_transact_type_fk']]['authamt'],
                    'ckeckout_date'=>$request[$request['acc_transact_type_fk']]['ckeckout_date'],
                    'card_type_code'=>$request[$request['acc_transact_type_fk']]['card_type_code'],
                    'card_type'=>$request[$request['acc_transact_type_fk']]['card_type'],
                    'card_owner_name'=>$request[$request['acc_transact_type_fk']]['card_owner_name'],
                    'authcode'=>$request[$request['acc_transact_type_fk']]['authcode'],
                    'all_grades_id'=>$request[$request['acc_transact_type_fk']]['all_grades_id'],
                    'checkout_area_code'=>$request[$request['acc_transact_type_fk']]['checkout_area_code'],
                    'checkout_area'=>$request[$request['acc_transact_type_fk']]['checkout_area'],
                    'installment'=>$request[$request['acc_transact_type_fk']]['installment'],
                    'requested'=>$request[$request['acc_transact_type_fk']]['requested'],
                    'card_nat'=>$request[$request['acc_transact_type_fk']]['card_nat'],
                    'checkout_mode'=>$request[$request['acc_transact_type_fk']]['checkout_mode'],
                    'created_at'=>date("Y-m-d H:i:s"),
                ]);
                break;

            // case ReceivedMethod::CreditCard3:
            //     $id = DB::table('acc_received_credit')->insertGetId([
            //         'installment'=>3,
            //         'created_at'=>date("Y-m-d H:i:s"),
            //     ]);
            //     break;

            case ReceivedMethod::Remittance:
                $id = DB::table('acc_received_remit')->insertGetId([
                    'remittance'=>$request[$request['acc_transact_type_fk']]['remittance'],
                    'memo'=>$request[$request['acc_transact_type_fk']]['bank_slip_name'],
                    'created_at'=>date("Y-m-d H:i:s"),
                ]);
                break;

            case ReceivedMethod::ForeignCurrency:
                $id = DB::table('acc_received_currency')->insertGetId([
                    'currency'=>$request[$request['acc_transact_type_fk']]['rate'],
                    'foreign_currency'=>$request[$request['acc_transact_type_fk']]['foreign_price'],
                    'created_at'=>date("Y-m-d H:i:s"),
                ]);
                break;

            // case ReceivedMethod::AccountsReceivable:

            // case ReceivedMethod::Other:

            // case ReceivedMethod::Refund:
        }

        return $id;
    }


    public static function update_received_method($request)
    {
        switch ($request['received_method']) {
            // case ReceivedMethod::Cash:

            case ReceivedMethod::Cheque:
                // DB::table('acc_received_cheque')->where('id', $request['received_method_id'])->update([
                //     'ticket_number'=>$request['ticket_number'],
                //     'due_date'=>$request['due_date'],
                //     'created_at'=>date("Y-m-d H:i:s"),
                // ]);
                break;

            case ReceivedMethod::CreditCard:
                $card_type = [
                    'visa'=>'VISA',
                    'jcb'=>'JCB',
                    'master'=>'MASTER',
                    'american_express'=>'美國運通卡',
                    'union_pay'=>'銀聯卡',
                ];
                $checkout_area = [
                    'taipei'=>'台北',
                ];

                DB::table('acc_received_credit')->where('id', $request['received_method_id'])->update([
                    'cardnumber'=>$request['cardnumber'],
                    // 'authamt'=>$request['authamt'],
                    'ckeckout_date'=>$request['ckeckout_date'],
                    'card_type_code'=>array_key_exists($request['card_type_code'], $card_type) ? $request['card_type_code'] : null,
                    'card_type'=>array_key_exists($request['card_type_code'], $card_type) ? $card_type[$request['card_type_code']] : null,
                    'card_owner_name'=>$request['card_owner_name'],
                    'authcode'=>$request['authcode'],
                    // 'all_grades_id'=>$request['all_grades_id'],
                    'checkout_area_code'=>array_key_exists($request['checkout_area_code'], $checkout_area) ? $request['checkout_area_code'] : null,
                    'checkout_area'=>array_key_exists($request['checkout_area_code'], $checkout_area) ? $checkout_area[$request['checkout_area_code']] : null,
                    // 'installment'=>$request['installment'],
                    // 'requested'=>$request['requested'],
                    // 'card_nat'=>$request['card_nat'],
                    // 'checkout_mode'=>$request['checkout_mode'],
                    'updated_at'=>date("Y-m-d H:i:s"),
                ]);
                break;

            // case ReceivedMethod::CreditCard3:
            //     DB::table('acc_received_credit')->where('id', $request['received_method_id'])->update([
            //         'installment'=>3,
            //         'created_at'=>date("Y-m-d H:i:s"),
            //     ]);
            //     break;

            case ReceivedMethod::Remittance:
                // DB::table('acc_received_remit')->where('id', $request['received_method_id'])->update([
                //     'remittance'=>$request['remittance'],
                //     'memo'=>$request['bank_slip_name'],
                //     'created_at'=>date("Y-m-d H:i:s"),
                // ]);
                break;

            case ReceivedMethod::ForeignCurrency:
                // DB::table('acc_received_currency')->where('id', $request['received_method_id'])->update([
                //     'currency'=>$request['rate'],
                //     'foreign_currency'=>$request['foreign_price'],
                //     'created_at'=>date("Y-m-d H:i:s"),
                // ]);
                break;

            // case ReceivedMethod::AccountsReceivable:

            // case ReceivedMethod::Other:

            // case ReceivedMethod::Refund:
        }

        DB::table('acc_received')->where('id', $request['received_id'])->update([
            'all_grades_id'=>$request['all_grades_id'],
            'updated_at'=>date("Y-m-d H:i:s"),
        ]);
    }


    public static function delete_received_order($id)
    {
        $target = self::findOrFail($id);

        if($target->receipt_date){
            $target = null;
        } else {
            $target->delete();
        }

        return $target;
    }


    public static function get_received_detail($received_order_id = [])
    {
        $query = DB::table('acc_received AS received')
            ->leftJoin('acc_received_credit AS _credit', function($join){
                $join->on('received.received_method_id', '=', '_credit.id');
                $join->where([
                    'received.received_method'=>'credit_card',
                ]);
            })
            ->leftJoin('acc_received_cheque AS _cheque', function($join){
                $join->on('received.received_method_id', '=', '_cheque.id');
                $join->where([
                    'received.received_method'=>'cheque',
                ]);
            })
            ->leftJoin('acc_received_currency AS _currency', function($join){
                $join->on('received.received_method_id', '=', '_currency.id');
                $join->where([
                    'received.received_method'=>'foreign_currency',
                ]);
            })
            ->leftJoin('acc_received_remit AS _remit', function($join){
                $join->on('received.received_method_id', '=', '_remit.id');
                $join->where([
                    'received.received_method'=>'remit',
                ]);
            })
            ->whereIn('received.received_order_id', $received_order_id)
            ->selectRaw('
                received.id AS received_id,
                received.received_order_id,
                received.received_method,
                received.received_method_id,
                received.all_grades_id,
                received.tw_price,
                received.accountant_id_fk,
                received.note
            ')
            ->selectRaw('
                _credit.cardnumber AS credit_card_number,
                _credit.authamt AS credit_card_price,
                _credit.ckeckout_date AS credit_card_ckeckout_date,
                _credit.card_type_code AS credit_card_type_code,
                _credit.card_type AS credit_card_type,
                _credit.card_owner_name AS credit_card_owner_name,
                _credit.authcode AS credit_card_authcode,
                _credit.checkout_area_code AS credit_card_area_code,
                _credit.checkout_area AS credit_card_area,
                _credit.installment AS credit_card_installment,
                _credit.requested AS credit_card_requested,
                _credit.card_nat AS credit_card_nat,
                _credit.checkout_mode AS credit_card_checkout_mode
            ')

            ->selectRaw('
                _cheque.ticket_number AS cheque_ticket_number,
                _cheque.due_date AS cheque_due_date
            ')

            ->selectRaw('
                _currency.currency AS currency,
                _currency.foreign_currency AS currency_foreign
            ')

            ->selectRaw('
                _remit.remittance AS remit_remittance,
                _remit.memo AS remit_memo
            ')
            ->get();

        foreach($query as $value){
            $value->received_method_name = ReceivedMethod::getDescription($value->received_method);
            $value->account = AllGrade::find($value->all_grades_id)->eachGrade;

            if($value->received_method == 'foreign_currency'){
                $arr = explode('-', AllGrade::find($value->all_grades_id)->eachGrade->name);
                $value->currency_name = $arr[0] == '外幣' ? $arr[1] . ' - ' . $arr[2] : 'NTD';
                $value->currency_rate = DB::table('acc_received_currency')->find($value->received_method_id)->currency;
            } else {
                $value->currency_name = 'NTD';
                $value->currency_rate = 1;
            }
        }

        return $query;
    }
}
