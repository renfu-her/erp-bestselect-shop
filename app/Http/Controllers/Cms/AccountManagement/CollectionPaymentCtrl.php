<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use App\Enums\Payable\ChequeStatus;
use App\Enums\Supplier\Payment;

use App\Models\AccountPayable;
use App\Models\AllGrade;
use App\Models\Consignment;
use App\Models\Customer;
use App\Models\DayEnd;
use App\Models\Delivery;
use App\Models\Depot;
use App\Models\GeneralLedger;
use App\Models\NotePayableOrder;
use App\Models\Order;
use App\Models\PayableAccount;
use App\Models\PayableCash;
use App\Models\PayableCheque;
use App\Models\PayableDefault;
use App\Models\PayableForeignCurrency;
use App\Models\PayableOther;
use App\Models\PayableRemit;
use App\Models\PayingOrder;
use App\Models\Purchase;
use App\Models\StituteOrder;
use App\Models\Supplier;
use App\Models\User;

class CollectionPaymentCtrl extends Controller
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
        $cond['source_sn'] = Arr::get($query, 'source_sn', null);

        $cond['po_min_price'] = Arr::get($query, 'po_min_price', null);
        $cond['po_max_price'] = Arr::get($query, 'po_max_price', null);
        $po_price = [
            $cond['po_min_price'],
            $cond['po_max_price']
        ];

        $cond['po_sdate'] = Arr::get($query, 'po_sdate', null);
        $cond['po_edate'] = Arr::get($query, 'po_edate', null);
        $po_payment_date = [
            $cond['po_sdate'],
            $cond['po_edate']
        ];

        $cond['check_balance'] = Arr::get($query, 'check_balance', 'all');

        $dataList = PayingOrder::paying_order_list(
            $cond['payee'],
            $cond['po_sn'],
            $cond['source_sn'],
            $po_price,
            $po_payment_date,
            $cond['check_balance']
        )->paginate($page)->appends($query);

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
                    $account_code = $value->po_product_grade_code ? $value->po_product_grade_code : '1000';
                    $account_name = $value->po_product_grade_name ? $value->po_product_grade_name : '無設定會計科目';
                    $product_name = $account_code . ' ' . $account_name;
                    foreach(json_decode($value->product_items) as $p_value){
                        $avg_price = $p_value->num == 0 ? 0 : $p_value->price / $p_value->num;
                        $name = $product_name . ' --- ' . $p_value->title . '（' . $avg_price . ' * ' . $p_value->num . '）';
                        $product_title = $p_value->title;

                        if($value->po_source_type == 'acc_stitute_orders' || $value->po_source_type == 'pcs_paying_orders'){
                            if($value->po_type == 1){
                                $product_account = AllGrade::find($p_value->all_grades_id) ? AllGrade::find($p_value->all_grades_id)->eachGrade : null;
                                $account_code = $product_account ? $product_account->code : '1000';
                                $account_name = $product_account ? $product_account->name : '無設定會計科目';
                                $product_title = $account_name;

                            } else if($value->po_type == 2){
                                $account_code = '';
                                $account_name = '';
                            }
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
                        $account_code = $d_value->grade_code ? $d_value->grade_code : '4000';
                        $account_name = $d_value->grade_name ? $d_value->grade_name : '無設定會計科目';
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

        $balance_status = [
            'all'=>'不限',
            '0'=>'未付款',
            '1'=>'已付款',
        ];

        return view('cms.account_management.collection_payment.list', [
            'data_per_page' => $page,
            'dataList' => $dataList,
            'cond' => $cond,
            'payee' => $payee_merged,
            'balance_status' => $balance_status,
        ]);
    }


    public function edit(Request $request, $id)
    {
        $paying_order = PayingOrder::findOrFail($id);

        if($request->isMethod('post')){
            $request->validate([
                'client_key' => 'required|string',
            ]);

            $client_key = explode('|', request('client_key'));

            if(count($client_key) > 1){
                $client = PayingOrder::payee($client_key[0], $client_key[1]);

                $paying_order->update([
                    'payee_id' =>$client->id,
                    'payee_name' =>$client->name,
                    'payee_phone' =>$client->phone,
                    'payee_address' =>$client->address,
                ]);

                wToast(__('付款單更新成功'));

                return redirect()->to(PayingOrder::paying_order_link($paying_order->source_type, $paying_order->source_id, $paying_order->source_sub_id, $paying_order->type));
            }

            wToast(__('付款單更新失敗', ['type'=>'danger']));
            return redirect()->back();
        }

        $user = User::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $customer = Customer::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $depot = Depot::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $supplier = Supplier::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $client_merged = array_merge($user, $customer, $depot, $supplier);

        return view('cms.account_management.collection_payment.edit', [
            'form_action'=>route('cms.collection_payment.edit', ['id'=>$id]),
            'client' => $client_merged,
            'paying_order' => $paying_order,
        ]);
    }


    public function destroy($id)
    {
        $po = PayingOrder::delete_paying_order($id);
        if($po){
            // reverse - source order
            if($po->source_type == app(Purchase::class)->getTable()){

            } else if($po->source_type == app(Order::class)->getTable() && $po->source_sub_id != null){

            } else if($po->source_type == app(Consignment::class)->getTable()){

            } else if($po->source_type == app(StituteOrder::class)->getTable()){
                $parm = [
                    'id' => $po->source_id,
                ];
                StituteOrder::update_stitute_order_approval($parm, true);

            } else if($po->source_type == app(Order::class)->getTable() && $po->source_sub_id == null){

            } else if($po->source_type == app(Delivery::class)->getTable()){

            } else if($po->source_type == app(PayingOrder::class)->getTable() && $po->type == 1){
                $parm = [
                    'append_pay_order_id' => $po->id,
                ];
                PayingOrder::update_account_payable_method($parm, true);

            } else if($po->source_type == app(PayingOrder::class)->getTable() && $po->type == 2){
                $append_po_id = PayingOrder::where('append_po_id', $po->id)->pluck('id')->toArray();
                $parm = [
                    'po_id' => $append_po_id,
                ];
                PayingOrder::update_paying_order_append_to($parm, true);
            }

            // cheque status is cashed then po can't delete,
            // if status not cashed then would not count in note payable order,
            // so only update cheque status as po_delete
            $payable_data = PayingOrder::get_payable_detail($id, 2);
            $parm = [
                'cheque_payable_id'=>$payable_data->pluck('cheque_id')->toArray(),
                'status_code'=>'po_delete',
                'status'=>'付款單刪除',
            ];
            NotePayableOrder::update_cheque_payable_method($parm);


            if($po->payment_date){
                DayEnd::match_day_end_status($po->payment_date, $po->sn);
            }

            wToast('刪除完成');

            return redirect()->to(PayingOrder::paying_order_source_link($po->source_type, $po->source_id, $po->source_sub_id, $po->type, true));

        } else {
            wToast('刪除失敗', ['type'=>'danger']);
            return redirect()->back();
        }
    }


    public function payable_list(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id' => 'required|exists:pcs_paying_orders,id',
        ]);

        $paying_order = PayingOrder::find($id);
        $payable_data = PayingOrder::get_payable_detail($id);
        $data_status_check = PayingOrder::payable_data_status_check($payable_data);
        $previous_url = PayingOrder::paying_order_link($paying_order->source_type, $paying_order->source_id, $paying_order->source_sub_id, $paying_order->type);

        return view('cms.account_management.collection_payment.payable_list', [
            'paying_order' => $paying_order,
            'payable_data' => $payable_data,
            'data_status_check' => $data_status_check,
            'previous_url' => $previous_url,
        ]);
    }


    public function payable_delete(Request $request, $payable_id)
    {
        $request->merge([
            'payable_id'=>$payable_id,
        ]);

        $request->validate([
            'payable_id' => 'required|exists:acc_payable,id',
        ]);

        $payable = AccountPayable::find($payable_id);
        $paying_order = PayingOrder::find($payable->pay_order_id);

        DB::beginTransaction();

        try {
            switch ($payable->acc_income_type_fk) {
                case Payment::Cash:
                    $payable_record = PayableCash::find($payable->payable_id);
                    break;
                case Payment::Cheque:
                    $payable_record = PayableCheque::find($payable->payable_id);
                    break;
                case Payment::Remittance:
                    $payable_record = PayableRemit::find($payable->payable_id);
                    break;
                case Payment::ForeignCurrency:
                    $payable_record = PayableForeignCurrency::find($payable->payable_id);
                    break;
                case Payment::AccountsPayable:
                    $payable_record = PayableAccount::find($payable->payable_id);
                    break;
                case Payment::Other:
                    $payable_record = PayableOther::find($payable->payable_id);
                    break;
            }

            $payable_record->delete();
            $payable->delete();

            if($paying_order->balance_date && $paying_order->payment_date){
                DayEnd::match_day_end_status($paying_order->payment_date, $paying_order->sn);

                $paying_order->update([
                    'balance_date'=>null,
                    'payment_date'=>null,
                ]);

                if($paying_order->source_type == app(PayingOrder::class)->getTable()){
                    if($paying_order->type == 1){
                        $accounts_payable_id = DB::table('acc_payable_account')->where('append_pay_order_id', $paying_order->id)->pluck('id')->toArray();

                        $parm = [
                            'action'=>'reverse',
                            'accounts_payable_id'=>$accounts_payable_id,
                            'status_code'=>0,
                        ];
                        PayingOrder::update_account_payable_method($parm);

                    } else if($paying_order->type == 2){
                        $append_po_id = PayingOrder::where('append_po_id', $paying_order->id)->pluck('id')->toArray();
                        $parm = [
                            'action'=>'reverse',
                            'po_id' => $append_po_id,
                        ];
                        PayingOrder::update_paying_order_append_to($parm);
                    }
                }
            }

            DB::commit();
            wToast('刪除完成');

        } catch (\Exception $e) {
            DB::rollback();
            wToast('刪除失敗', ['type'=>'danger']);
        }

        return redirect()->back();
    }


    public function claim(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'selected' => 'required|array',
                'selected.*' => 'exists:pcs_paying_orders,id',
                'po_id' => 'required|array',
                'po_id.*' => 'exists:pcs_paying_orders,id',
                'amt_net' => 'required|array',
                'amt_net.*' => 'required|numeric|between:0,9999999999.99',
            ]);

            $compare = array_diff(request('selected'), request('po_id'));
            if(count($compare) == 0){
                $source_type = app(PayingOrder::class)->getTable();
                $n_id = PayingOrder::withTrashed()->get()->count() + 1;
                $po_id = current(request('po_id'));

                $pre_paying_order = PayingOrder::findOrFail($po_id);
                $product_grade = PayableDefault::where('name', '=', 'product')->first()->default_grade_id;
                $logistics_grade = PayableDefault::where('name', '=', 'logistics')->first()->default_grade_id;

                $result = PayingOrder::createPayingOrder(
                    $source_type,
                    $n_id,
                    null,
                    $request->user()->id,
                    2,
                    $product_grade,
                    $logistics_grade,
                    array_sum(request('amt_net')),
                    '',
                    '',
                    $pre_paying_order->payee_id,
                    $pre_paying_order->payee_name,
                    $pre_paying_order->payee_phone,
                    $pre_paying_order->payee_address
                );

                $paying_order = PayingOrder::find($result['id']);

                $parm = [
                    'action'=>'new',
                    'po_id' => request('po_id'),
                    'balance_date' => $paying_order->balance_date,// = NULL
                    'payment_date' => $paying_order->payment_date,// = NULL
                    'append_po_id' => $paying_order->id,
                    'append_po_sn' => $paying_order->sn,
                ];
                PayingOrder::update_paying_order_append_to($parm);

                return redirect()->route('cms.collection_payment.po-edit', [
                    'id'=>$paying_order->id,
                ]);
            }

            wToast(__('付款單建立失敗', ['type'=>'danger']));
            return redirect()->back();
        }

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
        $cond['source_sn'] = Arr::get($query, 'source_sn', null);

        $cond['po_min_price'] = Arr::get($query, 'po_min_price', null);
        $cond['po_max_price'] = Arr::get($query, 'po_max_price', null);
        $po_price = [
            $cond['po_min_price'],
            $cond['po_max_price']
        ];

        $cond['po_sdate'] = Arr::get($query, 'po_sdate', null);
        $cond['po_edate'] = Arr::get($query, 'po_edate', null);
        $po_payment_date = [
            $cond['po_sdate'],
            $cond['po_edate']
        ];

        $cond['check_balance'] = Arr::get($query, 'check_balance', 'all');

        $dataList = PayingOrder::paying_order_list(
            $cond['payee'],
            $cond['po_sn'],
            $cond['source_sn'],
            $po_price,
            $po_payment_date,
            '0',
            true
        )
        // ->whereNull('po.append_po_id')
        ->whereRaw('CONCAT(po.source_type, "_", po.type) != "pcs_paying_orders_2"')
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
                    $account_code = $value->po_product_grade_code ? $value->po_product_grade_code : '1000';
                    $account_name = $value->po_product_grade_name ? $value->po_product_grade_name : '無設定會計科目';
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
                        $account_code = $d_value->grade_code ? $d_value->grade_code : '4000';
                        $account_name = $d_value->grade_name ? $d_value->grade_name : '無設定會計科目';
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

        $balance_status = [
            'all'=>'不限',
            '0'=>'未付款',
            '1'=>'已付款',
        ];

        return view('cms.account_management.collection_payment.pre_merge_list', [
            'form_action' => route('cms.collection_payment.claim'),
            'data_per_page' => $page,
            'dataList' => $dataList,
            'cond' => $cond,
            'payee' => $payee_merged,
            'balance_status' => $balance_status,
        ]);
    }


    public function po_edit(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id' => 'required|exists:pcs_paying_orders,id',
        ]);

        $append_po_sn = PayingOrder::where('append_po_id', $id)->pluck('sn')->toArray();
        $target_items = PayingOrder::paying_order_list(null, $append_po_sn, null, null, null, '0', true)->get();

        foreach($target_items as $data){
            if($data->po_source_type == 'pcs_purchase' && $data->po_type == 1){
                $tmp_po = PayingOrder::where([
                    'source_type'=>'pcs_purchase',
                    'source_id'=>$data->po_source_id,
                    'type'=>0,
                ])->first();

                if($tmp_po){
                    $d = (object)[
                        'product_owner'=>'',
                        'title'=>'訂金抵扣（訂金付款單號' . $tmp_po->sn . '）',
                        'sku'=>'',
                        'all_grades_id'=>"",
                        'grade_code'=>"1118",
                        'grade_name'=>"商品存貨",
                        'price'=>-$tmp_po->price,
                        'num'=>1,
                        'summary'=>$tmp_po->memo,
                        'memo'=>"",
                    ];

                    if($data->product_items){
                        $items = json_decode($data->product_items);
                        array_unshift($items, $d);
                        $data->product_items = json_encode($items, JSON_UNESCAPED_UNICODE);
                    } else {
                        $data->product_items = json_encode([$d], JSON_UNESCAPED_UNICODE);
                    }
                }
            }
        }

        $paying_order = PayingOrder::findOrFail($id);
        $payable_data = PayingOrder::get_payable_detail($id);

        $tw_price = $paying_order->price - $payable_data->sum('tw_price');

        $total_grades = GeneralLedger::total_grade_list();

        return view('cms.account_management.collection_payment.po_edit', [
            'breadcrumb_data' => ['id' => $id],
            'form_action' => route('cms.collection_payment.po-store', ['id' => $paying_order->id]),
            'previous_url' => route('cms.collection_payment.index'),
            'target_items' => $target_items,
            'paying_order' => $paying_order,
            'payable_data' => $payable_data,
            'tw_price' => $tw_price,
            'total_grades' => $total_grades,

            'cashDefault' => PayableDefault::where('name', 'cash')->pluck('default_grade_id')->toArray(),
            'chequeDefault' => PayableDefault::where('name', 'cheque')->pluck('default_grade_id')->toArray(),
            'remitDefault' => PayableDefault::where('name', 'remittance')->pluck('default_grade_id')->toArray(),
            'all_currency' => PayableDefault::getCurrencyOptionData()['selectedCurrencyResult']->toArray(),
            'currencyDefault' => PayableDefault::where('name', 'foreign_currency')->pluck('default_grade_id')->toArray(),
            'accountPayableDefault' => PayableDefault::where('name', 'accounts_payable')->pluck('default_grade_id')->toArray(),
            'otherDefault' => PayableDefault::where('name', 'other')->pluck('default_grade_id')->toArray(),

            'transactTypeList' => AccountPayable::getTransactTypeList(),
            'chequeStatus' => ChequeStatus::get_key_value(),
        ]);
    }


    public function po_store(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id' => 'required|exists:pcs_paying_orders,id',
            'acc_transact_type_fk' => 'required|regex:/^[1-6]$/',
            'tw_price' => 'required|numeric',
            'summary'=>'nullable|string',
            'note'=>'nullable|string',
        ]);

        $paying_order = PayingOrder::findOrFail($id);

        $request->merge([
            'pay_order_id'=>$paying_order->id,
        ]);

        $data = $request->except('_token');

        $payable_type = $data['acc_transact_type_fk'];
        switch ($payable_type) {
            case Payment::Cash:
                PayableCash::storePayableCash($data);
                break;
            case Payment::Cheque:
                $request->validate([
                    'cheque.ticket_number'=>'required|unique:acc_payable_cheque,ticket_number|regex:/^[A-Z]{2}[0-9]{7}$/'
                ]);
                PayableCheque::storePayableCheque($data);
                break;
            case Payment::Remittance:
                PayableRemit::storePayableRemit($data);
                break;
            case Payment::ForeignCurrency:
                PayableForeignCurrency::storePayableCurrency($data);
                break;
            case Payment::AccountsPayable:
                PayableAccount::storePayablePayableAccount($data);
                break;
            case Payment::Other:
                PayableOther::storePayableOther($data);
                break;
        }

        $payable_data = PayingOrder::get_payable_detail($id);
        if (count($payable_data) > 0 && $paying_order->price == $payable_data->sum('tw_price')) {
            $paying_order->update([
                'balance_date' => date('Y-m-d H:i:s'),
                'payment_date' => $data['payment_date'],
            ]);

            DayEnd::match_day_end_status($data['payment_date'], $paying_order->sn);
        }

        if (PayingOrder::find($paying_order->id) && PayingOrder::find($paying_order->id)->balance_date) {
            $po_id = PayingOrder::where('append_po_id', $id)->pluck('id')->toArray();

            $parm = [
                'action'=>'new',
                'po_id' => $po_id,
                'balance_date' => $paying_order->balance_date,
                'payment_date' => $paying_order->payment_date,
                'append_po_id' => $paying_order->id,
                'append_po_sn' => $paying_order->sn,
            ];
            PayingOrder::update_paying_order_append_to($parm);

            return redirect()->route('cms.collection_payment.po-show', [
                'id' => $id,
            ]);

        } else {
            return redirect()->route('cms.collection_payment.po-edit', [
                'id' => $id,
            ]);
        }
    }


    public function po_show(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id'=>'required|exists:pcs_paying_orders,id',
        ]);

        $applied_company = DB::table('acc_company')->where('id', 1)->first();

        $append_po_sn = PayingOrder::where('append_po_id', $id)->pluck('sn')->toArray();
        $target_items = PayingOrder::paying_order_list(null, $append_po_sn, null, null, null, '1', true)->get();

        foreach($target_items as $data){
            $data->po_url_link = PayingOrder::paying_order_link($data->po_source_type, $data->po_source_id, $data->po_source_sub_id, $data->po_type);
            $data->source_url_link = PayingOrder::paying_order_source_link($data->po_source_type, $data->po_source_id, $data->po_source_sub_id, $data->po_type);

            if($data->po_source_type == 'pcs_purchase' && $data->po_type == 1){
                $tmp_po = PayingOrder::where([
                    'source_type'=>'pcs_purchase',
                    'source_id'=>$data->po_source_id,
                    'type'=>0,
                ])->first();

                if($tmp_po){
                    $d = (object)[
                        'product_owner'=>'',
                        'title'=>'訂金抵扣（訂金付款單號' . $tmp_po->sn . '）',
                        'sku'=>'',
                        'all_grades_id'=>"",
                        'grade_code'=>"1118",
                        'grade_name'=>"商品存貨",
                        'price'=>-$tmp_po->price,
                        'num'=>1,
                        'summary'=>$tmp_po->memo,
                        'memo'=>"",
                    ];

                    if($data->product_items){
                        $items = json_decode($data->product_items);
                        array_unshift($items, $d);
                        $data->product_items = json_encode($items, JSON_UNESCAPED_UNICODE);
                    } else {
                        $data->product_items = json_encode([$d], JSON_UNESCAPED_UNICODE);
                    }
                }
            }
        }

        $paying_order = PayingOrder::findOrFail($id);
        $payable_data = PayingOrder::get_payable_detail($id);
        $data_status_check = PayingOrder::payable_data_status_check($payable_data);

        $zh_price = num_to_str($paying_order->price);

        if (!$paying_order->balance_date) {
            // return abort(404);

            return redirect()->route('cms.collection_payment.po-edit', [
                'id' => $id,
            ]);
        }

        $undertaker = User::find($paying_order->usr_users_id);

        $accountant = User::whereIn('id', $payable_data->pluck('accountant_id_fk')->toArray())->get();
        $accountant = array_unique($accountant->pluck('name')->toArray());
        asort($accountant);

        $view = 'cms.account_management.collection_payment.po_show';
        if (request('action') == 'print') {
            $view = 'doc.print_multiple_po';
        }

        return view($view, [
            'breadcrumb_data' => ['id' => $paying_order->id],
            'previous_url' => route('cms.collection_payment.claim'),
            'applied_company' => $applied_company,
            'paying_order' => $paying_order,
            'target_items' => $target_items,
            'target_po' => $target_items->pluck('po_url_link', 'po_sn')->toArray(),
            'payable_data' => $payable_data,
            'data_status_check' => $data_status_check,
            'zh_price' => $zh_price,
            'undertaker'=>$undertaker,
            'accountant'=>implode(',', $accountant),
        ]);
    }
}