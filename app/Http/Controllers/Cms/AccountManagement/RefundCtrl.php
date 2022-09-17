<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Enums\Supplier\Payment;

use App\Models\AllGrade;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Depot;
use App\Models\GeneralLedger;
use App\Models\Order;
use App\Models\PayingOrder;
use App\Models\Supplier;
use App\Models\User;

use Illuminate\Support\Arr;

class RefundCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();
        $page = getPageCount(Arr::get($query, 'data_per_page', 100)) > 0 ? getPageCount(Arr::get($query, 'data_per_page', 100)) : 100;

        $cond = [];

        $cond['payee_key'] = Arr::get($query, 'payee_key', null);
        if (gettype($cond['payee_key']) == 'string') {
            $key = explode('|', $cond['payee_key']);
            $cond['payee']['id'] = $key[0];
            $cond['payee']['name'] = $key[1];
        } else {
            $cond['payee'] = [];
        }

        $cond['po_sn'] = Arr::get($query, 'po_sn', null);

        $cond['po_created_sdate'] = Arr::get($query, 'po_created_sdate', null);
        $cond['po_created_edate'] = Arr::get($query, 'po_created_edate', null);
        $po_created_date = [
            $cond['po_created_sdate'],
            $cond['po_created_edate']
        ];

        $cond['check_balance'] = Arr::get($query, 'check_balance', 'all');

        $dataList = PayingOrder::paying_order_list(
            $cond['payee'],
            $cond['po_sn'],
            null,
            null,
            null,
            $cond['check_balance']
        )
        ->where(function ($q) use ($po_created_date) {
            $filter = [app(Order::class)->getTable(), app(Delivery::class)->getTable()];
            $q->whereIn('po.source_type', $filter);
            // $q->where('po.source_sub_id', null);
            $q->where('po.type', 9);

            if ($po_created_date) {
                $s_po_created = $po_created_date[0] ? date('Y-m-d', strtotime($po_created_date[0])) : null;
                $e_po_created = $po_created_date[1] ? date('Y-m-d', strtotime($po_created_date[1] . ' +1 day')) : null;

                if($s_po_created){
                    $q->where('po.created_at', '>=', $s_po_created);
                }
                if($e_po_created){
                    $q->where('po.created_at', '<', $e_po_created);
                }
            }
        })
        ->paginate($page)->appends($query);

        // accounting classification start
            foreach($dataList as $value){
                $debit = [];
                $credit = [];

                // 付款項目
                if($value->payable_list){
                    foreach(json_decode($value->payable_list) as $pay_v){
                        $payment_method_name = Payment::getDescription($pay_v->acc_income_type_fk);
                        $payment_account = AllGrade::find($pay_v->all_grades_id) ? AllGrade::find($pay_v->all_grades_id)->eachGrade : null;
                        $account_code = $payment_account ? $payment_account->code : '1000';
                        $account_name = $payment_account ? $payment_account->name : '無設定會計科目';

                        // if($pay_v->acc_income_type_fk == 4){
                        //     $arr = explode('-', AllGrade::find($pay_v->all_grades_id)->eachGrade->name);
                        //     $pay_v->currency_name = $arr[0] == '外幣' ? $arr[1] . ' - ' . $arr[2] : 'NTD';
                        //     $pay_v->currency_rate = DB::table('acc_payment_currency')->find($pay_v->payment_method_id)->currency;
                        // } else {
                        //     $pay_v->currency_name = 'NTD';
                        //     $pay_v->currency_rate = 1;
                        // }

                        $name = $payment_method_name . ' ' . $pay_v->summary . '（' . $account_code . ' ' . $account_name . '）';

                        $tmp = [
                            'account_code'=>$account_code,
                            'name'=>$name,
                            'price'=>$pay_v->tw_price,
                            'type'=>'p',
                            'd_type'=>'payable',

                            'account_name'=>$account_name,
                            'method_name'=>$payment_method_name,
                            'summary'=>$pay_v->summary,
                            'note'=>$pay_v->note,
                            'product_title'=>null,
                            'del_even'=>null,
                            'del_category_name'=>null,
                            'product_price'=>null,
                            'product_qty'=>null,
                            'product_owner'=>null,
                            'discount_title'=>null,
                            'payable_type'=>$pay_v->payable_type,
                            'received_info'=>null,
                        ];
                        GeneralLedger::classification_processing($debit, $credit, $tmp);
                    }
                }

                // 商品
                if($value->product_items){
                    $product_account = AllGrade::find($value->po_product_grade_id) ? AllGrade::find($value->po_product_grade_id)->eachGrade : null;
                    $account_code = $product_account ? $product_account->code : '1000';
                    $account_name = $product_account ? $product_account->name : '無設定會計科目';
                    $product_name = $account_code . ' ' . $account_name;
                    foreach(json_decode($value->product_items) as $p_value){
                        $avg_price = $p_value->price / $p_value->num;
                        $name = $product_name . ' --- ' . $p_value->title . '（' . $avg_price . ' * ' . $p_value->num . '）';
                        $product_title = $p_value->title;

                        if($value->po_source_type == 'acc_stitute_orders' || $value->po_source_type == 'pcs_paying_orders'){
                            $product_account = AllGrade::find($p_value->all_grades_id) ? AllGrade::find($p_value->all_grades_id)->eachGrade : null;
                            $account_code = $product_account ? $product_account->code : '1000';
                            $account_name = $product_account ? $product_account->name : '無設定會計科目';
                            $product_title = $account_name;
                        }

                        $tmp = [
                            'account_code'=>$account_code,
                            'name'=>$name,
                            'price'=>$p_value->price,
                            'type'=>'p',
                            'd_type'=>'product',

                            'account_name'=>$account_name,
                            'method_name'=>null,
                            'summary'=>null,
                            'note'=>null,
                            'product_title'=>$product_title,
                            'del_even'=>null,
                            'del_category_name'=>null,
                            'product_price'=>$avg_price,
                            'product_qty'=>$p_value->num,
                            'product_owner'=>$p_value->product_owner,
                            'discount_title'=>null,
                            'payable_type'=>null,
                            'received_info'=>null,
                        ];
                        GeneralLedger::classification_processing($debit, $credit, $tmp);
                    }
                }

                // 物流
                if($value->logistics_price <> 0){
                    $log_account = AllGrade::find($value->po_logistics_grade_id) ? AllGrade::find($value->po_logistics_grade_id)->eachGrade : null;
                    $account_code = $log_account ? $log_account->code : '5000';
                    $account_name = $log_account ? $log_account->name : '無設定會計科目';
                    $name = $account_code . ' ' . $account_name;

                    $tmp = [
                        'account_code'=>$account_code,
                        'name'=>$name,
                        'price'=>$value->logistics_price,
                        'type'=>'p',
                        'd_type'=>'logistics',

                        'account_name'=>$account_name,
                        'method_name'=>null,
                        'summary'=>null,
                        'note'=>null,
                        'product_title'=>null,
                        'del_even'=>null,
                        'del_category_name'=>null,
                        'product_price'=>null,
                        'product_qty'=>null,
                        'product_owner'=>null,
                        'discount_title'=>null,
                        'payable_type'=>null,
                        'received_info'=>null,
                    ];
                    GeneralLedger::classification_processing($debit, $credit, $tmp);
                }

                // 折扣
                if($value->discount_value > 0){
                    foreach(json_decode($value->order_discount) ?? [] as $d_value){
                        $dis_account = AllGrade::find($d_value->discount_grade_id) ? AllGrade::find($d_value->discount_grade_id)->eachGrade : null;
                        $account_code = $dis_account ? $dis_account->code : '4000';
                        $account_name = $dis_account ? $dis_account->name : '無設定會計科目';
                        $name = $account_code . ' ' . $account_name;

                        $tmp = [
                            'account_code'=>$account_code,
                            'name'=>$name,
                            'price'=>$d_value->discount_value,
                            'type'=>'p',
                            'd_type'=>'discount',

                            'account_name'=>$account_name,
                            'method_name'=>null,
                            'summary'=>null,
                            'note'=>null,
                            'product_title'=>null,
                            'del_even'=>null,
                            'del_category_name'=>null,
                            'product_price'=>null,
                            'product_qty'=>null,
                            'product_owner'=>null,
                            'discount_title'=>$d_value->title,
                            'payable_type'=>null,
                            'received_info'=>null,
                        ];
                        GeneralLedger::classification_processing($debit, $credit, $tmp);
                    }
                }


                $value->debit = $debit;
                $value->credit = $credit;

                $value->po_url_link = PayingOrder::paying_order_link($value->po_source_type, $value->po_source_id, $value->po_source_sub_id, $value->po_type);
                $value->source_url_link = PayingOrder::paying_order_source_link($value->po_source_type, $value->po_source_id, $value->po_source_sub_id, $value->po_type);
                if($value->po_source_type == 'pcs_purchase'){
                    $value->po_url_link = "javascript:void(0);";
                }
            }
        // accounting classification end

        $user = User::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $customer = Customer::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $depot = Depot::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $supplier = Supplier::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $payee_merged = array_merge($user, $customer, $depot, $supplier);

        $check_balance_status = [
            'all'=>'不限',
            '0'=>'未付款',
            '1'=>'已付款',
        ];

        return view('cms.account_management.refund.list', [
            'data_per_page' => $page,
            'dataList' => $dataList,
            'cond' => $cond,
            'payee' => $payee_merged,
            'check_balance_status' => $check_balance_status,
        ]);
    }
}