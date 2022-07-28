<?php

namespace App\Http\Controllers\Cms\AccountManagement;



use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use App\Enums\Supplier\Payment;

use App\Models\AllGrade;
use App\Models\AccountPayable;
use App\Models\Customer;
use App\Models\Depot;
use App\Models\Order;
use App\Models\PayableAccount;
use App\Models\PayableCash;
use App\Models\PayableCheque;
use App\Models\PayableForeignCurrency;
use App\Models\PayableOther;
use App\Models\PayableRemit;
use App\Models\PayingOrder;
use App\Models\Supplier;
use App\Models\GeneralLedger;
use App\Models\PayableDefault;

class AccountPayableCtrl extends Controller
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

        $cond['p_order_sn'] = Arr::get($query, 'p_order_sn', null);
        $cond['purchase_sn'] = Arr::get($query, 'purchase_sn', null);

        $cond['p_order_min_price'] = Arr::get($query, 'p_order_min_price', null);
        $cond['p_order_max_price'] = Arr::get($query, 'p_order_max_price', null);
        $p_order_price = [
            $cond['p_order_min_price'],
            $cond['p_order_max_price']
        ];

        $cond['p_order_sdate'] = Arr::get($query, 'p_order_sdate', null);
        $cond['p_order_edate'] = Arr::get($query, 'p_order_edate', null);
        $p_order_payment_date = [
            $cond['p_order_sdate'],
            $cond['p_order_edate']
        ];

        $dataList = PayingOrder::paying_order_list(
            $cond['payee'],
            $cond['p_order_sn'],
            $cond['purchase_sn'],
            $p_order_price,
            $p_order_payment_date,
        )->paginate($page)->appends($query);

        // dd($dataList);

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

                    $name = $payment_method_name . ' ' . $pay_v->summary . '（' . $account_code . ' - ' . $account_name . '）';

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
            $product_account = AllGrade::find($value->po_product_grade_id) ? AllGrade::find($value->po_product_grade_id)->eachGrade : null;
            $account_code = $product_account ? $product_account->code : '1000';
            $account_name = $product_account ? $product_account->name : '無設定會計科目';
            $product_name = $account_code . ' - ' . $account_name;
            if($value->product_list){
                foreach(json_decode($value->product_list) as $pro_v){
                    $avg_price = $pro_v->price / $pro_v->num;
                    $name = $product_name . ' --- ' . $pro_v->title . '（' . $avg_price . ' * ' . $pro_v->num . '）';

                    $tmp = [
                        'account_code'=>$account_code,
                        'name'=>$name,
                        'price'=>$pro_v->price,
                        'type'=>'p',
                        'd_type'=>'product',

                        'account_name'=>$account_name,
                        'method_name'=>null,
                        'summary'=>null,
                        'note'=>null,
                        'product_title'=>$pro_v->title,
                        'del_even'=>null,
                        'del_category_name'=>null,
                        'product_price'=>$avg_price,
                        'product_qty'=>$pro_v->num,
                        'product_owner'=>$pro_v->product_owner,
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
                $name = $account_code . ' - ' . $account_name;

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

            $value->debit = $debit;
            $value->credit = $credit;
        }
        // accounting classification end

        $depot = Depot::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $customer = Customer::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $supplier = Supplier::whereNull('deleted_at')->select('id', 'name')->get()->toArray();

        $payee_merged = array_merge($customer, $depot, $supplier);

        return view('cms.account_management.account_payable.list', [
            'data_per_page' => $page,
            'dataList' => $dataList,
            'cond' => $cond,
            'payee' => $payee_merged,
        ]);
    }
}
