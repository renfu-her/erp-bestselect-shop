<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use App\Enums\Payable\ChequeStatus;
use App\Enums\Received\ReceivedMethod;
use App\Enums\Supplier\Payment;

use App\Models\AccountPayable;
use App\Models\AllGrade;
use App\Models\Consignment;
use App\Models\Customer;
use App\Models\DayEnd;
use App\Models\Delivery;
use App\Models\Depot;
use App\Models\GeneralLedger;
use App\Models\Logistic;
use App\Models\NotePayableOrder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PayableAccount;
use App\Models\PayableCash;
use App\Models\PayableCheque;
use App\Models\PayableDefault;
use App\Models\PayableForeignCurrency;
use App\Models\PayableOther;
use App\Models\PayableRemit;
use App\Models\PayingOrder;
use App\Models\Petition;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\ReceivedOrder;
use App\Models\ReceivedRefund;
use App\Models\StituteOrder;
use App\Models\StituteOrderItem;
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
                if($value->po_source_type == app(PayingOrder::class)->getTable() && $value->po_type == 2){
                    if($value->product_items){
                        $tmp_po_sn_array = collect(json_decode($value->product_items))->pluck('title')->toArray();
                        $tmp_po = PayingOrder::paying_order_list(null, $tmp_po_sn_array, null, null, null, 'all', true)->get();
                        $tmp_merge = [];

                        foreach($tmp_po as $po_value){
                            $product = [];
                            $logistics = [];
                            $discount = [];

                            if($po_value->product_items){
                                $product = json_decode($po_value->product_items);
                            }
                            if($po_value->logistics_price <> 0){
                                $logistics = [(object)[
                                    'product_owner'=>'',
                                    'title'=>$po_value->logistics_summary,
                                    'sku'=>'',
                                    'all_grades_id'=>$po_value->po_logistics_grade_id,
                                    'grade_code'=>$po_value->po_logistics_grade_code,
                                    'grade_name'=>$po_value->po_logistics_grade_name,
                                    'price'=>$po_value->logistics_price,
                                    'num'=>1,
                                    'summary'=>$po_value->logistics_summary,
                                    'memo'=>$po_value->logistics_memo,
                                ]];
                            }
                            if($po_value->discount_value > 0){
                                $discount = json_decode(str_replace('"price":"', '"price":"-', $po_value->order_discount));
                            }

                            $tmp_merge = array_merge($tmp_merge, $product, $logistics, $discount);
                        }

                        $value->product_items = json_encode($tmp_merge, JSON_UNESCAPED_UNICODE);
                    }
                }

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

                        if(in_array($value->po_source_type, ['acc_stitute_orders', 'dlv_delivery', 'pcs_paying_orders', 'ord_received_orders'])) {
                            if($value->po_type == 1){
                                $product_account = AllGrade::find($p_value->all_grades_id) ? AllGrade::find($p_value->all_grades_id)->eachGrade : null;
                                $account_code = $product_account ? $product_account->code : '1000';
                                $account_name = $product_account ? $product_account->name : '無設定會計科目';
                                $product_title = $account_name;

                            } else if($value->po_type == 9 || $value->po_type == 2){
                                $account_code = $p_value->grade_code;
                                $account_name = $p_value->grade_name;
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
                        'summary'=>$value->logistics_summary,
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

        $user = User::whereNull('deleted_at')->select('id', 'name', 'title')->get()->toArray();
        $customer = Customer::whereNull('deleted_at')->select('id', 'name', 'email')->get()->toArray();
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

            wToast(__('付款單更新失敗'), ['type'=>'danger']);
            return redirect()->back();
        }

        $user = User::whereNull('deleted_at')->select('id', 'name', 'title')->get()->toArray();
        $customer = Customer::whereNull('deleted_at')->select('id', 'name', 'email')->get()->toArray();
        $depot = Depot::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $supplier = Supplier::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $client_merged = array_merge($user, $customer, $depot, $supplier);

        return view('cms.account_management.collection_payment.edit', [
            'form_action'=>route('cms.collection_payment.edit', ['id'=>$id]),
            'client' => $client_merged,
            'paying_order' => $paying_order,
        ]);
    }


    public function edit_note(Request $request, $id)
    {
        $paying_order = PayingOrder::findOrFail($id);

        if($request->isMethod('post')){
            $request->validate([
                'item' => 'nullable|array',
                'logistic_item' => 'nullable|array',
            ]);

            DB::beginTransaction();

            try {
                if($paying_order->source_type == 'pcs_purchase'){
                    if (request('item') && is_array(request('item'))) {
                        $purchase_item = request('item');
                        foreach ($purchase_item as $key => $value) {
                            $value['purchase_item_id'] = $key;
                            PurchaseItem::update_purchase_item($value);
                        }
                    }

                    if (request('logistic_item') && is_array(request('logistic_item'))) {
                        $logistic_item = request('logistic_item');
                        foreach ($logistic_item as $key => $value) {
                            $value['logistic_id'] = $key;
                            Purchase::update_logistic($value);
                        }
                    }

                } else if($paying_order->source_type == 'ord_orders' && $paying_order->source_sub_id != null){
                    if (request('logistic_item') && is_array(request('logistic_item'))) {
                        $logistic_item = request('logistic_item');
                        foreach ($logistic_item as $key => $value) {
                            $value['logistic_id'] = $key;
                            Logistic::update_logistic($value);
                        }
                    }

                } else if($paying_order->source_type == 'csn_consignment'){
                    if (request('logistic_item') && is_array(request('logistic_item'))) {
                        $logistic_item = request('logistic_item');
                        foreach ($logistic_item as $key => $value) {
                            $value['logistic_id'] = $key;
                            Logistic::update_logistic($value);
                        }
                    }

                } else if($paying_order->source_type == 'acc_stitute_orders'){
                    if (request('item') && is_array(request('item'))) {
                        $stitute_order_item = request('item');
                        foreach ($stitute_order_item as $key => $value) {
                            $value['stitute_order_item_id'] = $key;
                            StituteOrderItem::update_stitute_order_item($value);
                        }
                    }

                } else if($paying_order->source_type == 'ord_orders' && $paying_order->source_sub_id == null){
                    if (request('item') && is_array(request('item'))) {
                        $order_item = request('item');
                        foreach ($order_item as $key => $value) {
                            $value['order_item_id'] = $key;
                            OrderItem::update_order_item($value);
                        }
                    }

                } else if($paying_order->source_type == 'dlv_delivery'){
                    if (request('item') && is_array(request('item'))) {
                        $order_item = request('item');
                        foreach ($order_item as $key => $value) {
                            $value['order_item_id'] = $key;
                            OrderItem::update_order_item($value);
                        }
                    }

                } else if($paying_order->source_type == 'ord_received_orders'){
                    if (request('item') && is_array(request('item'))) {
                        $refund_item = request('item');
                        foreach ($refund_item as $key => $value) {
                            $value['refund_item_id'] = $key;
                            ReceivedRefund::update_refund_item($value);
                        }
                    }
                }

                DB::commit();
                wToast(__('付款項目備註更新成功'));
                return redirect()->to(PayingOrder::paying_order_link($paying_order->source_type, $paying_order->source_id, $paying_order->source_sub_id, $paying_order->type));

            } catch (\Exception $e) {
                DB::rollback();
                wToast(__('付款項目備註更新失敗', ['type' => 'danger']));
            }

            return redirect()->back();

        } else if ($request->isMethod('get')) {

            $item_data = [];
            $logistic_data = [];

            if($paying_order->source_type == 'pcs_purchase'){
                $purchase = Purchase::purchase_item($paying_order->source_id)->get();
                foreach ($purchase as $key => $value) {
                    $purchase[$key]->purchase_table_items = json_decode($value->purchase_table_items);
                }
                $purchase = $purchase->first();

                foreach($purchase->purchase_table_items as $value){
                    $item_data[] = (object)[
                        'item_id' => $value->id,
                        'title' => $value->product_title,
                        'price' => ($value->total_price / $value->qty),
                        'qty' => $value->qty,
                        'total_price' => $value->total_price,
                        'note' => $value->memo,
                        'po_note' => $value->po_note,
                    ];
                }

                if($purchase->purchase_logistics_price <> 0){
                    $logistic_data[] = (object)[
                        'item_id' => $purchase->purchase_id,
                        'title' => AllGrade::find($paying_order->logistics_grade_id)->eachGrade->name,
                        'price' => $purchase->purchase_logistics_price,
                        'qty' => 1,
                        'total_price' => $purchase->purchase_logistics_price,
                        'note' => $purchase->purchase_logistics_memo,
                        'po_note' => $purchase->purchase_logistics_po_note,
                    ];
                }

            } else if($paying_order->source_type == 'ord_orders' && $paying_order->source_sub_id != null){
                $sub_order = Order::subOrderDetail($paying_order->source_id, $paying_order->source_sub_id, true)->get()->toArray()[0];

                $logistic_data[] = (object)[
                    'item_id' => $sub_order->logistic_id,
                    'title' => AllGrade::find($paying_order->logistics_grade_id)->eachGrade->name . ' ' . $sub_order->ship_group_name . ' #' . ($sub_order->projlgt_order_sn ?? $sub_order->package_sn),
                    'price' => $sub_order->logistic_cost,
                    'qty' => 1,
                    'total_price' => $sub_order->logistic_cost,
                    'note' => $sub_order->logistic_memo,
                    'po_note' => $sub_order->logistic_po_note,
                ];

            } else if($paying_order->source_type == 'csn_consignment'){
                $consignment_data  = Consignment::getDeliveryData($paying_order->source_id)->get()->first();

                $logistic_data[] = (object)[
                    'item_id' => $consignment_data->lgt_id,
                    'title' => AllGrade::find($paying_order->logistics_grade_id)->eachGrade->name . ' ' . $consignment_data->group_name . ' #' . ($consignment_data->projlgt_order_sn ?? $consignment_data->package_sn),
                    'price' => $consignment_data->lgt_cost,
                    'qty' => 1,
                    'total_price' => $consignment_data->lgt_cost,
                    'note' => $consignment_data->lgt_memo,
                    'po_note' => $consignment_data->lgt_po_note,
                ];

            } else if($paying_order->source_type == 'acc_stitute_orders'){
                $stitute_order = StituteOrder::stitute_order_list($paying_order->source_id)->first();

                foreach(json_decode($stitute_order->so_items) as $value){
                    $item_data[] = (object)[
                        'item_id' =>$value->id,
                        'title' =>$value->summary,
                        'price' =>$value->price,
                        'qty' =>$value->qty,
                        'total_price' =>$value->total_price,
                        'note' =>$value->memo,
                        'po_note' =>$value->po_note,
                    ];
                }

            } else if($paying_order->source_type == 'ord_orders' && $paying_order->source_sub_id == null){
                $order_item = OrderItem::item_order($paying_order->source_id)->get();

                foreach($order_item as $value){
                    $item_data[] = (object)[
                        'item_id' =>$value->order_item_id,
                        'title' =>$value->product_title,
                        'price' =>$value->product_price,
                        'qty' =>$value->product_qty,
                        'total_price' =>$value->product_origin_price,
                        'note' =>$value->product_note,
                        'po_note' =>$value->product_po_note,
                    ];
                }

            } else if($paying_order->source_type == 'dlv_delivery'){
                $delivery = Delivery::back_item($paying_order->source_id)->get();
                foreach ($delivery as $key => $value) {
                    $delivery[$key]->delivery_back_items = json_decode($value->delivery_back_items);
                }
                $delivery = $delivery->first();

                foreach($delivery->delivery_back_items as $value){
                    if($value->event_item_id){
                        $item_data[] = (object)[
                            'item_id' => $value->event_item_id,
                            'title' => $value->product_title,
                            'price' => $value->price,
                            'qty' => $value->qty,
                            'total_price' => $value->total_price,
                            'note' => $value->note,
                            'po_note' => $value->po_note,
                        ];
                    }
                }

            } else if($paying_order->source_type == 'ord_received_orders'){

                $refund_items = ReceivedRefund::refund_list(null, null, null, $id)->get();

                foreach($refund_items as $value){
                    $item_data[] = (object)[
                        'item_id' => $value->refund_id,
                        'title' => $value->refund_title . ' ' . $value->refund_summary,
                        'price' => $value->refund_price,
                        'qty' => $value->refund_qty,
                        'total_price' => $value->refund_total_price,
                        'note' => $value->refund_note,
                        'po_note' => $value->refund_po_note,
                    ];
                }
            }

            return view('cms.account_management.collection_payment.edit_note', [
                'form_action' => route('cms.collection_payment.edit_note', ['id' => $id]),
                'paying_order' => $paying_order,
                'item_data' => $item_data,
                'logistic_data' => $logistic_data,
            ]);
        }
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

            wToast(__('付款單建立失敗'), ['type'=>'danger']);
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
                        $avg_price = $p_value->num == 0 ? 0 : $p_value->price / $p_value->num;
                        $name = $product_name . ' --- ' . $p_value->title . '（' . $avg_price . ' * ' . $p_value->num . '）';
                        $product_title = $p_value->title;

                        if(in_array($value->po_source_type, ['acc_stitute_orders', 'dlv_delivery', 'pcs_paying_orders', 'ord_received_orders'])) {
                            if($value->po_type == 1){
                                $product_account = AllGrade::find($p_value->all_grades_id) ? AllGrade::find($p_value->all_grades_id)->eachGrade : null;
                                $account_code = $product_account ? $product_account->code : '1000';
                                $account_name = $product_account ? $product_account->name : '無設定會計科目';
                                $product_title = $account_name;

                            } else if($value->po_type == 9 || $value->po_type == 2){
                                $account_code = $p_value->grade_code;
                                $account_name = $p_value->grade_name;
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
                        'summary'=>$value->logistics_summary,
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

        $user = User::whereNull('deleted_at')->select('id', 'name', 'title')->get()->toArray();
        $customer = Customer::whereNull('deleted_at')->select('id', 'name', 'email')->get()->toArray();
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
            'undertaker' => $undertaker,
            'accountant' => implode(',', $accountant),
            'relation_order' => Petition::getBindedOrder($paying_order->id, 'ISG'),
        ]);
    }


    public function refund_po_show(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id'=>'required|exists:ord_received_orders,id',
        ]);

        $received_order = ReceivedOrder::findOrFail($id);
        $received_data = ReceivedOrder::get_received_detail($id, 'refund');

        $source_id = $id;
        $source_type = $received_order->getTable();

        // create
        $paying_order = PayingOrder::where([
                'source_type' => $source_type,
                'source_id' => $source_id,
                'source_sub_id' => null,
                'type' => 9
            ])->first();

        if(! $paying_order){
            DB::beginTransaction();

            try {
                $product_grade = PayableDefault::where('name', '=', 'product')->first()->default_grade_id;
                $logistics_grade = PayableDefault::where('name', '=', 'logistics')->first()->default_grade_id;
                $price = abs($received_data->sum('tw_price'));

                $result = PayingOrder::createPayingOrder(
                    $source_type,
                    $source_id,
                    null,
                    $request->user()->id,
                    9,
                    $product_grade,
                    $logistics_grade,
                    $price,
                    '',
                    '',
                    $received_order->drawee_id,
                    $received_order->drawee_name,
                    $received_order->drawee_phone,
                    $received_order->drawee_address
                );
                $paying_order = PayingOrder::find($result['id']);

                // copy
                $data = [];
                foreach($received_data as $r_value){
                    $grade = AllGrade::find($r_value->all_grades_id)->eachGrade;
                    $data[] = [
                        'title' => '退還銷貨收入',
                        'grade_id' => $r_value->all_grades_id,
                        'grade_code' => $grade->code,
                        'grade_name' => $grade->name,
                        'price' => abs($r_value->tw_price),
                        'qty' => 1,
                        'total_price' => abs($r_value->tw_price) * 1,
                        'taxation' => $r_value->taxation,
                        'summary' => $r_value->summary,
                        'note' => $r_value->note,
                        'source_ro_id' => $received_order->id,
                        'source_ro_sn' => $received_order->sn,
                        'append_po_id' => $paying_order->id,
                        'append_po_sn' => $paying_order->sn,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    // remove
                    DB::table('acc_received')->where('id', $r_value->received_id)->delete();
                }
                if (count($data) > 0) {
                    ReceivedRefund::insert($data);

                    // adjust
                    if($received_order->source_type == 'ord_orders'){
                        $n_received_data = ReceivedOrder::get_received_detail($received_order->id);
                        $r_method_arr = $n_received_data->pluck('received_method')->toArray();
                        $r_method_title_arr = [];
                        foreach($r_method_arr as $v){
                            array_push($r_method_title_arr, ReceivedMethod::getDescription($v));
                        }
                        $r_method['value'] = implode(',', $r_method_arr);
                        $r_method['description'] = implode(',', $r_method_title_arr);
                        Order::change_order_payment_status($received_order->source_id, null, (object) $r_method);

                        $received_order->update([
                            'price' => $received_order->price + $price
                        ]);
                    }
                }

                DB::commit();

            } catch (\Exception $e) {
                DB::rollback();
                wToast(__('新增退出付款單失敗'), ['type'=>'danger']);
                return redirect()->back();
            }
        }

        $payable_data = PayingOrder::get_payable_detail($paying_order->id);
        $data_status_check = PayingOrder::payable_data_status_check($payable_data);

        $zh_price = num_to_str($paying_order->price);

        $applied_company = DB::table('acc_company')->where('id', 1)->first();

        $target_items = ReceivedRefund::refund_list(null, null, null, $paying_order->id)->get();

        $parent_source[] = [
            'sn' => $received_order->sn,
            'url' => PayingOrder::paying_order_source_link($paying_order->source_type, $paying_order->source_id, null, 9),
        ];
        if($received_order->source_type == 'ord_orders'){
            $order = Order::find($received_order->source_id);
            $parent_source[] = [
                'sn' => $order->sn,
                'url' => route('cms.order.detail', ['id' => $order->id]),
            ];
        }

        $undertaker = User::find($paying_order->usr_users_id);

        $accountant = User::whereIn('id', $payable_data->pluck('accountant_id_fk')->toArray())->get();
        $accountant = array_unique($accountant->pluck('name')->toArray());
        asort($accountant);

        $view = 'cms.account_management.collection_payment.refund_po_show';
        if (request('action') == 'print') {
            $view = 'doc.print_refund_po';
        }

        return view($view, [
            'applied_company' => $applied_company,
            'paying_order' => $paying_order,
            'target_items' => $target_items,
            'parent_source' => $parent_source,
            'payable_data' => $payable_data,
            'data_status_check' => $data_status_check,
            'zh_price' => $zh_price,
            'undertaker' => $undertaker,
            'accountant' => implode(',', $accountant),
            'relation_order' => Petition::getBindedOrder($paying_order->id, 'ISG'),
        ]);
    }


    public function refund_po_edit(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id' => 'required|exists:pcs_paying_orders,id',
        ]);

        $paying_order = PayingOrder::findOrFail($id);
        $payable_data = PayingOrder::get_payable_detail($id);
        $target_items = ReceivedRefund::refund_list(null, null, null, $paying_order->id)->get();

        $tw_price = $paying_order->price - $payable_data->sum('tw_price');

        $total_grades = GeneralLedger::total_grade_list();

        return view('cms.account_management.collection_payment.refund_po_edit', [
            'form_action' => route('cms.collection_payment.refund-po-store', ['id' => $paying_order->id]),
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


    public function refund_po_store(Request $request, $id)
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

            return redirect()->route('cms.collection_payment.refund-po-show', [
                'id' => $paying_order->source_id,
            ]);

        } else {
            return redirect()->route('cms.collection_payment.refund-po-edit', [
                'id' => $id,
            ]);
        }
    }
}