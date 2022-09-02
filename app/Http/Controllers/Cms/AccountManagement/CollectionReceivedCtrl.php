<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Enums\Received\ReceivedMethod;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use App\Models\AllGrade;
use App\Models\Customer;
use App\Models\Depot;
use App\Models\GeneralLedger;
use App\Models\Order;
use App\Models\ReceivedOrder;
use App\Models\Supplier;
use App\Models\User;

class CollectionReceivedCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();
        $page = getPageCount(Arr::get($query, 'data_per_page', 100)) > 0 ? getPageCount(Arr::get($query, 'data_per_page', 100)) : 100;

        $check_review_status = [
            'all'=>'不限',
            '0'=>'入款未審核',
            '1'=>'入款已審核',
        ];

        $cond = [];

        $cond['drawee_key'] = Arr::get($query, 'drawee_key', null);
        if (gettype($cond['drawee_key']) == 'string') {
            $key = explode('|', $cond['drawee_key']);
            $cond['drawee']['id'] = $key[0];
            $cond['drawee']['name'] = $key[1];
        } else {
            $cond['drawee'] = [];
        }

        $cond['ro_sn'] = Arr::get($query, 'ro_sn', null);
        $cond['source_sn'] = Arr::get($query, 'source_sn', null);

        $cond['r_order_min_price'] = Arr::get($query, 'r_order_min_price', null);
        $cond['r_order_max_price'] = Arr::get($query, 'r_order_max_price', null);
        $r_order_price = [
            $cond['r_order_min_price'],
            $cond['r_order_max_price']
        ];

        $cond['r_order_sdate'] = Arr::get($query, 'r_order_sdate', null);
        $cond['r_order_edate'] = Arr::get($query, 'r_order_edate', null);
        $r_order_receipt_date = [
            $cond['r_order_sdate'],
            $cond['r_order_edate']
        ];

        $cond['order_sdate'] = Arr::get($query, 'order_sdate', null);
        $cond['order_edate'] = Arr::get($query, 'order_edate', null);
        $received_date = [
            $cond['order_sdate'],
            $cond['order_edate']
        ];

        $cond['check_review'] = Arr::get($query, 'check_review', 'all');

        $dataList = ReceivedOrder::received_order_list(
            $cond['drawee'],
            $cond['ro_sn'],
            $cond['source_sn'],
            $r_order_price,
            $r_order_receipt_date,
            $received_date,
            $cond['check_review'],
        )->paginate($page)->appends($query);

        // accounting classification start
            foreach($dataList as $value){
                $debit = [];
                $credit = [];

                // 收款項目
                if($value->received_list){
                    foreach(json_decode($value->received_list) as $r_value){
                        $received_method_name = ReceivedMethod::getDescription($r_value->received_method);
                        $received_account = AllGrade::find($r_value->all_grades_id)->eachGrade;

                        if($r_value->received_method == 'foreign_currency'){
                            $arr = explode('-', AllGrade::find($r_value->all_grades_id)->eachGrade->name);
                            $r_value->currency_name = $arr[0] == '外幣' ? $arr[1] . ' - ' . $arr[2] : 'NTD';
                            $r_value->currency_rate = DB::table('acc_received_currency')->find($r_value->received_method_id)->currency;
                        } else {
                            $r_value->currency_name = 'NTD';
                            $r_value->currency_rate = 1;
                        }

                        $name = $received_method_name . ' ' . $r_value->summary . '（' . $received_account->code . ' - ' . $received_account->name . '）';

                        $tmp = [
                            'account_code'=>$received_account->code,
                            'name'=>$name,
                            'price'=>$r_value->tw_price,
                            'type'=>'r',
                            'd_type'=>'received',

                            'account_name'=>$received_account->name,
                            'method_name'=>$received_method_name,
                            'summary'=>$r_value->summary,
                            'note'=>$r_value->note,
                            'product_title'=>null,
                            'del_even'=>null,
                            'del_category_name'=>null,
                            'product_price'=>null,
                            'product_qty'=>null,
                            'product_owner'=>null,
                            'discount_title'=>null,
                            'payable_type'=>null,
                            'received_info'=>$r_value,
                        ];
                        GeneralLedger::classification_processing($debit, $credit, $tmp);
                    }
                }

                // 商品
                if($value->order_items){
                    $product_account = AllGrade::find($value->ro_product_grade_id) ? AllGrade::find($value->ro_product_grade_id)->eachGrade : null;
                    $account_code = $product_account ? $product_account->code : '4000';
                    $account_name = $product_account ? $product_account->name : '無設定會計科目';
                    $product_name = $account_code . ' ' . $account_name;
                    foreach(json_decode($value->order_items) as $o_value){
                        $name = $product_name . ' --- ' . $o_value->product_title . '（' . $o_value->price . ' * ' . $o_value->qty . '）';
                        $product_title = $o_value->product_title;

                        if($value->ro_source_type == 'ord_received_orders' || $value->ro_source_type == 'acc_request_orders'){
                            $product_account = AllGrade::find($o_value->all_grades_id) ? AllGrade::find($o_value->all_grades_id)->eachGrade : null;
                            $account_code = $product_account ? $product_account->code : '4000';
                            $account_name = $product_account ? $product_account->name : '無設定會計科目';
                            $product_title = $account_name;
                        }

                        $tmp = [
                            'account_code'=>$account_code,
                            'name'=>$name,
                            'price'=>$o_value->origin_price,
                            'type'=>'r',
                            'd_type'=>'product',

                            'account_name'=>$account_name,
                            'method_name'=>null,
                            'summary'=>null,
                            'note'=>null,
                            'product_title'=>$product_title,
                            'del_even'=>null,
                            'del_category_name'=>null,
                            'product_price'=>$o_value->price,
                            'product_qty'=>$o_value->qty,
                            'product_owner'=>null,
                            'discount_title'=>null,
                            'payable_type'=>null,
                            'received_info'=>null,
                        ];
                        GeneralLedger::classification_processing($debit, $credit, $tmp);
                    }
                }

                // 物流
                if($value->order_dlv_fee <> 0){
                    $log_account = AllGrade::find($value->ro_logistics_grade_id) ? AllGrade::find($value->ro_logistics_grade_id)->eachGrade : null;
                    $account_code = $log_account ? $log_account->code : '4000';
                    $account_name = $log_account ? $log_account->name : '無設定會計科目';
                    $name = $account_code . ' ' . $account_name;

                    $tmp = [
                        'account_code'=>$account_code,
                        'name'=>$name,
                        'price'=>$value->order_dlv_fee,
                        'type'=>'r',
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
                if($value->order_discount_value > 0){
                    foreach(json_decode($value->order_discount) ?? [] as $d_value){
                        $dis_account = AllGrade::find($d_value->discount_grade_id) ? AllGrade::find($d_value->discount_grade_id)->eachGrade : null;
                        $account_code = $dis_account ? $dis_account->code : '4000';
                        $account_name = $dis_account ? $dis_account->name : '無設定會計科目';
                        $name = $account_code . ' ' . $account_name;

                        $tmp = [
                            'account_code'=>$account_code,
                            'name'=>$name,
                            'price'=>$d_value->discount_value,
                            'type'=>'r',
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

                $value->link = ReceivedOrder::received_order_link($value->ro_source_type, $value->ro_source_id);
            }
        // accounting classification end

        $user = User::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $customer = Customer::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $depot = Depot::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $supplier = Supplier::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $drawee_merged = array_merge($user, $customer, $depot, $supplier);

        return view('cms.account_management.collection_received.list', [
            'data_per_page' => $page,
            'dataList' => $dataList,
            'cond' => $cond,
            'drawee' => $drawee_merged,
            'check_review_status' => $check_review_status,
        ]);
    }


    public function edit(Request $request, $id)
    {
        $received_order = ReceivedOrder::findOrFail($id);

        if($request->isMethod('post')){
            $request->validate([
                'client_key' => 'required|string',
            ]);

            $client_key = explode('|', request('client_key'));

            if(count($client_key) > 1){
                $client = ReceivedOrder::drawee($client_key[0], $client_key[1]);

                $received_order->update([
                    'drawee_id' => $client->id,
                    'drawee_name' => $client->name,
                    'drawee_phone' => $client->phone,
                    'drawee_address' => $client->address,
                ]);

                wToast(__('收款單更新成功'));

                return redirect()->to(ReceivedOrder::received_order_link($received_order->source_type, $received_order->source_id));
            }

            wToast(__('收款單更新失敗', ['type'=>'danger']));
            return redirect()->back();
        }

        $user = User::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $customer = Customer::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $depot = Depot::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $supplier = Supplier::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $client_merged = array_merge($user, $customer, $depot, $supplier);

        return view('cms.account_management.collection_received.edit', [
            'form_action'=>route('cms.collection_received.edit', ['id'=>$id]),
            'client' => $client_merged,
            'received_order' => $received_order,
        ]);
    }


    public function destroy($id)
    {
        $po = ReceivedOrder::delete_paying_order($id);
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

            } else if($po->source_type == app(ReceivedOrder::class)->getTable()){
                $parm = [
                    'append_pay_order_id'=>$po->id,
                ];
                ReceivedOrder::update_account_payable_method($parm, true);
            }

            // cheque status is cashed then po can't delete,
            // if status not cashed then would not count in note payable order,
            // so needn't update it
            //

            if($po->payment_date){
                DayEnd::match_day_end_status($po->payment_date, $po->sn);
            }

            wToast('刪除完成');

        } else {
            wToast('刪除失敗', ['type'=>'danger']);
        }

        return redirect()->to(ReceivedOrder::paying_order_source_link($po->source_type, $po->source_id, $po->source_sub_id, $po->type, true));
    }
}
