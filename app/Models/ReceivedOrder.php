<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\DB;

use App\Enums\Received\ReceivedMethod;
use App\Enums\Delivery\Event;
use App\Enums\Order\UserAddrType;

class ReceivedOrder extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'ord_received_orders';
    protected $guarded = [];


    public static function store_received($request)
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
                    'created_at'=>date("Y-m-d H:i:s"),
                ]);
                break;

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
}
