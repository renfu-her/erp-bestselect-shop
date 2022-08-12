<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Enums\Area\Area;
use App\Enums\Received\ReceivedMethod;
use App\Enums\Received\ChequeStatus;

use App\Models\CrdCreditCard;
use App\Models\GeneralLedger;
use App\Models\OrderPayCreditCard;
use App\Models\ReceivedDefault;
use App\Models\ReceivedOrder;
use App\Models\User;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AccountReceivedCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();
        $page = getPageCount(Arr::get($query, 'data_per_page', 100)) > 0 ? getPageCount(Arr::get($query, 'data_per_page', 100)) : 100;

        $cond = [];

        $cond['account_received_grade_id'] = Arr::get($query, 'account_received_grade_id', []);
        if (gettype($cond['account_received_grade_id']) == 'string') {
            $cond['account_received_grade_id'] = explode(',', $cond['account_received_grade_id']);
        } else {
            $cond['account_received_grade_id'] = [];
        }

        // $cond['authamt_min_price'] = Arr::get($query, 'authamt_min_price', null);
        // $cond['authamt_max_price'] = Arr::get($query, 'authamt_max_price', null);
        // $authamt_price = [
        //     $cond['authamt_min_price'],
        //     $cond['authamt_max_price']
        // ];
        $authamt_price = null;

        $cond['ro_created_sdate'] = Arr::get($query, 'ro_created_sdate', null);
        $cond['ro_created_edate'] = Arr::get($query, 'ro_created_edate', null);
        $ro_created_date = [
            $cond['ro_created_sdate'],
            $cond['ro_created_edate']
        ];

        $cond['status_code'] = Arr::get($query, 'status_code', null);

        $data_list = ReceivedOrder::get_account_received_list(
                [],
                $cond['status_code'],
                null,
                $cond['account_received_grade_id'],
                $authamt_price,
                $ro_created_date
            )->paginate($page)->appends($query);

        $account_received_grade = ReceivedDefault::leftJoinSub(GeneralLedger::getAllGrade(), 'grade', function($join) {
                $join->on('grade.primary_id', 'acc_received_default.default_grade_id');
            })
            ->select(
                'acc_received_default.name',
                'grade.primary_id as grade_id',
                'grade.code as grade_code',
                'grade.name as grade_name'
            )
            ->where('acc_received_default.name', 'account_received')
            ->get();

        return view('cms.account_management.account_received.list', [
            'data_per_page' => $page,
            'data_list' => $data_list,
            'cond' => $cond,
            'account_received_grade' => $account_received_grade,
        ]);
    }


    public function claim(Request $request, $type, $id, $key = null)
    {
        $request->merge([
            'type'=>$type,
            'id'=>$id,
            'key'=>$key,
        ]);

        $request->validate([
            'type' => 'required|in:g,t',
            'id' => 'required',
            'key' => 'required',
        ]);

        $grade_id = $type == 'g' ? $id : null;
        $ro_target = $type == 't' ? [$id, $key] : null;

        if($request->isMethod('post')){
            $request->validate([
                'selected' => 'required|array',
                'selected.*' => 'exists:acc_received_account,id',
                'account_received_id' => 'required|array',
                'account_received_id.*' => 'exists:acc_received_account,id',
                'amt_net' => 'required|array',
                'amt_net.*' => 'required|numeric|between:0,9999999999.99',
            ]);

            $compare = array_diff(request('selected'), request('account_received_id'));
            if(count($compare) == 0){
                $source_type = app(ReceivedOrder::class)->getTable();
                // $n_id = DB::select("SHOW TABLE STATUS FROM 'shop-dev' LIKE '" . $source_type . "'")[0]->Auto_increment;
                $n_id = ReceivedOrder::get()->count() + 1;
                $account_received_id = current(request('account_received_id'));
                $received = DB::table('acc_received')->where([
                    'received_method'=>'account_received',
                    'received_method_id'=>$account_received_id
                ])->first();

                $received_order = ReceivedOrder::create_received_order($source_type, $n_id, array_sum(request('amt_net')), $received->received_order_id);

                $parm = [
                    'account_received_id'=>request('account_received_id'),
                    'status_code'=>0,
                    'append_received_order_id'=>$received_order->id,
                    'sn'=>$received_order->sn,
                    'amt_net'=>request('amt_net'),
                ];
                ReceivedOrder::update_account_received_method($parm);

                return redirect()->route('cms.account_received.ro-edit', [
                    'id'=>$received_order->id,
                ]);
            }

            wToast(__('應收帳款收款單建立失敗', ['type'=>'danger']));
            return redirect()->back();
        }

        $data_list = ReceivedOrder::get_account_received_list([], 0, null, $grade_id, null, null, $ro_target)->get();

        return view('cms.account_management.account_received.claim', [
            'form_action'=>route('cms.account_received.claim', ['type'=>$type, 'id'=>$id, 'key'=>$key]),
            'data_list'=>$data_list,
        ]);
    }


    public function ro_edit(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id'=>'required|exists:ord_received_orders,id',
        ]);


        $account_received_id = DB::table('acc_received_account')->where('append_received_order_id', $id)->pluck('id')->toArray();
        $order_list_data = ReceivedOrder::get_account_received_list($account_received_id, 0)->get();

        $received_order = ReceivedOrder::findOrFail($id);
        $received_data = ReceivedOrder::get_received_detail($id);

        $tw_price = $received_order->price - $received_data->sum('tw_price');

        // grade process start
            $defaultData = [];
            foreach (ReceivedMethod::asArray() as $receivedMethod) {
                $defaultData[$receivedMethod] = DB::table('acc_received_default')->where('name', '=', $receivedMethod)
                    ->doesntExistOr(function () use ($receivedMethod) {
                        return DB::table('acc_received_default')->where('name', '=', $receivedMethod)
                            ->select('default_grade_id')
                            ->get();
                    });
            }

            $total_grades = GeneralLedger::total_grade_list();
            $allGradeArray = [];

            foreach ($total_grades as $grade) {
                $allGradeArray[$grade['primary_id']] = $grade;
            }
            $default_grade = [];
            foreach ($defaultData as $recMethod => $ids) {
                if ($ids !== true &&
                    $recMethod !== 'other') {
                    foreach ($ids as $id) {
                        $default_grade[$recMethod][$id->default_grade_id] = [
                            // 'methodName' => $recMethod,
                            'method' => ReceivedMethod::getDescription($recMethod),
                            'grade_id' => $id->default_grade_id,
                            'grade_num' => $allGradeArray[$id->default_grade_id]['grade_num'],
                            'code' => $allGradeArray[$id->default_grade_id]['code'],
                            'name' => $allGradeArray[$id->default_grade_id]['name'],
                        ];
                    }
                } else {
                    if($recMethod == 'other'){
                        $default_grade[$recMethod] = $allGradeArray;
                    } else {
                        $default_grade[$recMethod] = [];
                    }
                }
            }


            $currencyDefault = DB::table('acc_currency')
                ->leftJoin('acc_received_default', 'acc_currency.received_default_fk', '=', 'acc_received_default.id')
                ->select(
                    'acc_currency.name as currency_name',
                    'acc_currency.id as currency_id',
                    'acc_currency.rate',
                    'default_grade_id',
                    'acc_received_default.name as method_name'
                )
                ->orderBy('acc_currency.id')
                ->get();
            $currency_default_grade = [];
            foreach ($currencyDefault as $default) {
                $currency_default_grade[$default->default_grade_id][] = [
                    'currency_id'    => $default->currency_id,
                    'currency_name'    => $default->currency_name,
                    'rate'             => $default->rate,
                    'default_grade_id' => $default->default_grade_id,
                ];
            }
        // grade process end

        $card_type = CrdCreditCard::distinct('title')->groupBy('title')->orderBy('id', 'asc')->pluck('title', 'id')->toArray();

        $checkout_area = Area::get_key_value();

        return view('cms.account_management.account_received.ro_edit', [
            'form_action'=>route('cms.account_received.ro-store', ['id'=>$received_order->id]),
            'purchaser' => count($order_list_data) > 0 ? $order_list_data[0]->ro_target_name : null,
            'order_list_data' => $order_list_data,

            'received_order' => $received_order,
            'received_data' => $received_data,
            'tw_price' => $tw_price,
            'default_grade' => $default_grade,
            'currency_default_grade' => $currency_default_grade,
            'receivedMethods' => ReceivedMethod::asSelectArray(),
            'card_type'=>$card_type,
            'checkout_area'=>$checkout_area,
        ]);
    }


    public function ro_store(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id' => 'required|exists:ord_received_orders,id',
            'acc_transact_type_fk' => 'required|string|in:' . implode(',', ReceivedMethod::asArray()),
            'tw_price' => 'required|numeric',
            request('acc_transact_type_fk') => 'required|array',
            request('acc_transact_type_fk') . '.grade' => 'required|exists:acc_all_grades,id',
            'summary'=>'nullable|string',
            'note'=>'nullable|string',
        ]);

        $data = $request->except('_token');

        $source_type = app(ReceivedOrder::class)->getTable();
        $account_received_id = DB::table('acc_received_account')->where('append_received_order_id', $id)->pluck('id')->toArray();
        $received_order = ReceivedOrder::findOrFail($id);
        $received_order_id = $id;

        DB::beginTransaction();

        try {
            // 'credit_card'
            if($data['acc_transact_type_fk'] == ReceivedMethod::CreditCard){
                $card_type = CrdCreditCard::distinct('title')->groupBy('title')->orderBy('id', 'asc')->pluck('title', 'id')->toArray();

                $checkout_area = Area::get_key_value();

                $data[$data['acc_transact_type_fk']] = [
                    'cardnumber'=>$data[$data['acc_transact_type_fk']]['cardnumber'],
                    'authamt'=>$data['tw_price'] ?? 0,
                    'checkout_date'=>$data[$data['acc_transact_type_fk']]['checkout_date'] ?? null,// date('Y-m-d H:i:s')
                    'card_type_code'=>$data[$data['acc_transact_type_fk']]['card_type_code'] ?? null,
                    'card_type'=>$card_type[$data[$data['acc_transact_type_fk']]['card_type_code']] ?? null,
                    'card_owner_name'=>$data[$data['acc_transact_type_fk']]['card_owner_name'] ?? null,
                    'authcode'=>$data[$data['acc_transact_type_fk']]['authcode'] ?? null,
                    'all_grades_id'=>$data[$data['acc_transact_type_fk']]['grade'],
                    'checkout_area_code'=>'taipei',// $data[$data['acc_transact_type_fk']]['credit_card_area_code']
                    'checkout_area'=>'台北',// $checkout_area[$data[$data['acc_transact_type_fk']]['credit_card_area_code']]
                    'installment'=>$data[$data['acc_transact_type_fk']]['installment'] ?? 'none',
                    'status_code'=>0,
                    'card_nat'=>'local',
                    'checkout_mode'=>'offline',
                ];

                $data[$data['acc_transact_type_fk']]['grade'] = $data[$data['acc_transact_type_fk']]['all_grades_id'];

                $EncArray['more_info'] = $data[$data['acc_transact_type_fk']];

            } else if($data['acc_transact_type_fk'] == ReceivedMethod::AccountsReceivable){
                //
            }

            $result_id = ReceivedOrder::store_received_method($data);

            $parm = [];
            $parm['received_order_id'] = $received_order_id;
            $parm['received_method'] = $data['acc_transact_type_fk'];
            $parm['received_method_id'] = $result_id;
            $parm['grade_id'] = $data[$data['acc_transact_type_fk']]['grade'];
            $parm['price'] = $data['tw_price'];
            // $parm['accountant_id_fk'] = auth('user')->user()->id;
            $parm['summary'] = $data['summary'];
            $parm['note'] = $data['note'];
            ReceivedOrder::store_received($parm);

            if($data['acc_transact_type_fk'] == ReceivedMethod::CreditCard){
                OrderPayCreditCard::create_log($source_type, $data['id'], (object) $EncArray);
            }

            DB::commit();
            wToast(__('收款單儲存成功'));

        } catch (\Exception $e) {
            DB::rollback();
            wToast(__('收款單儲存失敗', ['type'=>'danger']));
        }


        if (ReceivedOrder::find($received_order_id) && ReceivedOrder::find($received_order_id)->balance_date) {
            $parm = [
                'account_received_id'=>$account_received_id,
                'status_code'=>1,
                'append_received_order_id'=>$received_order->id,
                'sn'=>$received_order->sn,
            ];
            ReceivedOrder::update_account_received_method($parm);

            return redirect()->route('cms.account_received.ro-receipt', [
                'id' => $data['id'],
            ]);

        } else {
            return redirect()->route('cms.account_received.ro-edit', [
                'id' => $data['id'],
            ]);
        }
    }


    public function ro_receipt(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id'=>'required|exists:ord_received_orders,id',
        ]);

        $account_received_id = DB::table('acc_received_account')->where('append_received_order_id', $id)->pluck('id')->toArray();
        $order_list_data = ReceivedOrder::get_account_received_list($account_received_id, 1)->get();

        $received_order = ReceivedOrder::findOrFail($id);
        $received_data = ReceivedOrder::get_received_detail($id);
        $data_status_check = false;
        foreach($received_data as $rd_value){
            if($rd_value->credit_card_status_code == 2 || $rd_value->cheque_status_code == 'cashed'){
                $data_status_check = true;
            }
        }

        if (!$received_order->balance_date) {
            // return abort(404);

            return redirect()->route('cms.account_received.ro-edit', [
                'id' => $id,
            ]);
        }

        $purchaser = $order_list_data->first();
        $undertaker = User::find($received_order->usr_users_id);

        // $accountant = User::whereIn('id', $received_data->pluck('accountant_id_fk')->toArray())->get();
        // $accountant = array_unique($accountant->pluck('name')->toArray());
        // asort($accountant);
        $accountant = User::find($received_order->accountant_id) ? User::find($received_order->accountant_id)->name : null;

        $zh_price = num_to_str($received_order->price);

        return view('cms.account_management.account_received.ro_receipt', [
            'order_list_data' => $order_list_data,
            'received_order' => $received_order,
            'received_data' => $received_data,
            'data_status_check' => $data_status_check,
            'purchaser' => $purchaser,
            'undertaker'=>$undertaker,
            // 'accountant'=>implode(',', $accountant),
            'accountant'=>$accountant,
            'zh_price' => $zh_price,
        ]);
    }


    public function ro_review(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);
        $request->validate([
            'id' => 'required|exists:ord_received_orders,id',
        ]);

        $received_order = ReceivedOrder::findOrFail($id);

        if (!$received_order || !$received_order->balance_date) {
            return abort(404);
        }

        if($request->isMethod('post')){
            $request->validate([
                'receipt_date' => 'required|date_format:"Y-m-d"',
                'invoice_number' => 'nullable|string',
            ]);

            DB::beginTransaction();

            try {
                $received_order->update([
                    'accountant_id'=>auth('user')->user()->id,
                    'receipt_date'=>request('receipt_date'),
                    'invoice_number'=>request('invoice_number'),
                ]);

                if(is_array(request('received_method'))){
                    $unique_m = array_unique(request('received_method'));

                    foreach($unique_m as $m_value){
                        if( in_array($m_value, ReceivedMethod::asArray()) && is_array(request($m_value))){
                            $req = request($m_value);
                            foreach($req as $r){
                                $r['received_method'] = $m_value;
                                ReceivedOrder::update_received_method($r);
                            }
                        }
                    }
                }

                DB::commit();
                wToast(__('入帳日期更新成功'));

                return redirect()->route('cms.account_received.ro-receipt', ['id'=>request('id')]);

            } catch (\Exception $e) {
                DB::rollback();
                wToast(__('入帳日期更新失敗', ['type'=>'danger']));

                return redirect()->back();
            }

        } else if($request->isMethod('get')){
            $received_data = ReceivedOrder::get_received_detail($id);
            $data_status_check = false;
            foreach($received_data as $rd_value){
                if($rd_value->credit_card_status_code == 2 || $rd_value->cheque_status_code == 'cashed'){
                    $data_status_check = true;
                }
            }

            if($received_order->receipt_date){
                if($data_status_check){
                    return redirect()->back();
                }

                $received_order->update([
                    'accountant_id'=>null,
                    'receipt_date'=>null,
                ]);

                wToast(__('入帳日期已取消'));
                return redirect()->route('cms.account_received.ro-receipt', ['id'=>request('id')]);

            } else {
                $undertaker = User::find($received_order->usr_users_id);

                $account_received_id = DB::table('acc_received_account')->where('append_received_order_id', $id)->pluck('id')->toArray();
                $order_list_data = ReceivedOrder::get_account_received_list($account_received_id, 1)->get();

                $debit = [];
                $credit = [];

                // 收款項目
                foreach($received_data as $value){
                    $name = $value->received_method_name . ' ' . $value->summary . '（' . $value->account->code . ' - ' . $value->account->name . '）';
                    // GeneralLedger::classification_processing($debit, $credit, $value->master_account->code, $name, $value->tw_price, 'r', 'received');

                    $tmp = [
                        'account_code'=>$value->account->code,
                        'name'=>$name,
                        'price'=>$value->tw_price,
                        'type'=>'r',
                        'd_type'=>'received',

                        'account_name'=>$value->account->name,
                        'method_name'=>$value->received_method_name,
                        'summary'=>$value->summary,
                        'note'=>$value->note,
                        'product_title'=>null,
                        'del_even'=>null,
                        'del_category_name'=>null,
                        'product_price'=>null,
                        'product_qty'=>null,
                        'product_owner'=>null,
                        'discount_title'=>null,
                        'payable_type'=>null,

                        'received_info'=>$value,
                    ];
                    GeneralLedger::classification_processing($debit, $credit, $tmp);
                }

                // 商品
                foreach($order_list_data as $value){
                    $account_code = '4000';
                    $account_name = '無設定會計科目';
                    $name = $value->ro_received_grade_code . ' ' . $value->ro_received_grade_name;

                    $tmp = [
                        'account_code'=>$account_code,
                        'name'=>$name,
                        'price'=>$value->account_amt_net,
                        'type'=>'r',
                        'd_type'=>'product',

                        'account_name'=>$account_name,
                        'method_name'=>null,
                        'summary'=>$value->summary ?? null,
                        'note'=>$value->note ?? null,
                        'product_title'=>$value->ro_received_grade_code . ' ' . $value->ro_received_grade_name,
                        'del_even'=>$value->del_even ?? null,
                        'del_category_name'=>$value->del_category_name ?? null,
                        'product_price'=>$value->tw_price,
                        'product_qty'=>1,
                        'product_owner'=>null,
                        'discount_title'=>null,
                        'payable_type'=>null,
                        'received_info'=>null,
                    ];
                    GeneralLedger::classification_processing($debit, $credit, $tmp);
                }

                $card_type = CrdCreditCard::distinct('title')->groupBy('title')->orderBy('id', 'asc')->pluck('title', 'id')->toArray();

                $checkout_area = Area::get_key_value();

                // grade process start
                    $defaultData = [];
                    foreach (ReceivedMethod::asArray() as $receivedMethod) {
                        $defaultData[$receivedMethod] = DB::table('acc_received_default')->where('name', '=', $receivedMethod)
                            ->doesntExistOr(function () use ($receivedMethod) {
                                return DB::table('acc_received_default')->where('name', '=', $receivedMethod)
                                    ->select('default_grade_id')
                                    ->get();
                            });
                    }

                    $total_grades = GeneralLedger::total_grade_list();
                    $allGradeArray = [];

                    foreach ($total_grades as $grade) {
                        $allGradeArray[$grade['primary_id']] = $grade;
                    }
                    $default_grade = [];
                    foreach ($defaultData as $recMethod => $ids) {
                        if ($ids !== true &&
                            $recMethod !== 'other') {
                            foreach ($ids as $id) {
                                $default_grade[$recMethod][$id->default_grade_id] = [
                                    // 'methodName' => $recMethod,
                                    'method' => ReceivedMethod::getDescription($recMethod),
                                    'grade_id' => $id->default_grade_id,
                                    'grade_num' => $allGradeArray[$id->default_grade_id]['grade_num'],
                                    'code' => $allGradeArray[$id->default_grade_id]['code'],
                                    'name' => $allGradeArray[$id->default_grade_id]['name'],
                                ];
                            }
                        } else {
                            if($recMethod == 'other'){
                                $default_grade[$recMethod] = $allGradeArray;
                            } else {
                                $default_grade[$recMethod] = [];
                            }
                        }
                    }

                    $currencyDefault = DB::table('acc_currency')
                        ->leftJoin('acc_received_default', 'acc_currency.received_default_fk', '=', 'acc_received_default.id')
                        ->select(
                            'acc_currency.name as currency_name',
                            'acc_currency.id as currency_id',
                            'acc_currency.rate',
                            'default_grade_id',
                            'acc_received_default.name as method_name'
                        )
                        ->orderBy('acc_currency.id')
                        ->get();
                    $currency_default_grade = [];
                    foreach ($currencyDefault as $default) {
                        $currency_default_grade[$default->default_grade_id][] = [
                            'currency_id'    => $default->currency_id,
                            'currency_name'    => $default->currency_name,
                            'rate'             => $default->rate,
                            'default_grade_id' => $default->default_grade_id,
                        ];
                    }
                // grade process end

                $cheque_status = ChequeStatus::get_key_value();

                return view('cms.account_management.account_received.ro_review', [
                    'form_action'=>route('cms.account_received.ro-review' , ['id'=>request('id')]),
                    'received_order'=>$received_order,
                    'order_list_data'=>$order_list_data,
                    'received_data'=>$received_data,
                    'undertaker'=>$undertaker,
                    'debit'=>$debit,
                    'credit'=>$credit,
                    'card_type'=>$card_type,
                    'checkout_area'=>$checkout_area,
                    'cheque_status'=>$cheque_status,
                    'credit_card_grade'=>$default_grade[ReceivedMethod::CreditCard],
                    'cheque_grade'=>$default_grade[ReceivedMethod::Cheque],
                    // 'default_grade'=>$default_grade,
                    // 'currency_default_grade'=>$currency_default_grade,

                    'breadcrumb_data' => ['id'=>$received_order->id],
                ]);
            }
        }
    }


    public function ro_taxation(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);
        $request->validate([
            'id' => 'required|exists:ord_received_orders,id',
        ]);

        $received_order = ReceivedOrder::findOrFail($id);

        if (!$received_order || !$received_order->balance_date) {
            return abort(404);
        }

        if($request->isMethod('post')){
            $request->validate([
                'received' => 'required|array',
                'product' => 'required|array',
            ]);

            DB::beginTransaction();

            try {
                $received_order->update([
                    'product_grade_id'=>request('product_grade_id'),
                ]);

                if(request('received') && is_array(request('received'))){
                    $received = request('received');
                    foreach($received as $key => $value){
                        $value['received_id'] = $key;
                        ReceivedOrder::update_received($value);
                    }
                }

                if(request('product') && is_array(request('product'))){
                    $product = request('product');
                    foreach($product as $key => $value){
                        $value['received_id'] = $key;
                        ReceivedOrder::update_received($value);
                    }
                }

                DB::commit();
                wToast(__('摘要/稅別更新成功'));

            } catch (\Exception $e) {
                DB::rollback();
                wToast(__('摘要/稅別更新失敗', ['type'=>'danger']));
            }

            return redirect()->route('cms.account_received.ro-receipt', ['id'=>request('id')]);

        } else if($request->isMethod('get')){
            $account_received_id = DB::table('acc_received_account')->where('append_received_order_id', $id)->pluck('id')->toArray();
            $order_list_data = ReceivedOrder::get_account_received_list($account_received_id, 1)->get();

            $received_data = ReceivedOrder::get_received_detail($id);

            $total_grades = GeneralLedger::total_grade_list();

            return view('cms.account_management.account_received.ro_taxation', [
                'form_action'=>route('cms.account_received.ro-taxation' , ['id'=>request('id')]),
                'received_order'=>$received_order,
                'order_list_data'=>$order_list_data,
                'received_data'=>$received_data,
                'total_grades'=>$total_grades,

                'breadcrumb_data'=>['id'=>$received_order->id],
            ]);
        }
    }
}