<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Enums\Area\Area;
use App\Enums\Received\ReceivedMethod;
use App\Enums\Received\ChequeStatus;

use App\Models\AllGrade;
use App\Models\Customer;
use App\Models\CrdCreditCard;
use App\Models\Depot;
use App\Models\GeneralLedger;
use App\Models\OrderPayCreditCard;
use App\Models\ReceivedOrder;
use App\Models\RequestOrder;
use App\Models\Supplier;
use App\Models\User;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RequestOrderCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();
        $page = getPageCount(Arr::get($query, 'data_per_page', 100)) > 0 ? getPageCount(Arr::get($query, 'data_per_page', 100)) : 100;

        $cond = [];

        $cond['client_key'] = Arr::get($query, 'client_key', null);
        if (gettype($cond['client_key']) == 'string') {
            $key = explode('|', $cond['client_key']);
            $cond['client']['id'] = $key[0];
            $cond['client']['name'] = $key[1];
        } else {
            $cond['client'] = [];
        }

        $cond['request_sn'] = Arr::get($query, 'request_sn', null);
        $cond['source_sn'] = Arr::get($query, 'source_sn', null);

        $cond['request_min_price'] = Arr::get($query, 'request_min_price', null);
        $cond['request_max_price'] = Arr::get($query, 'request_max_price', null);
        $request_price = [
            $cond['request_min_price'],
            $cond['request_max_price']
        ];

        $cond['request_sdate'] = Arr::get($query, 'request_sdate', null);
        $cond['request_edate'] = Arr::get($query, 'request_edate', null);
        $request_posting_date = [
            $cond['request_sdate'],
            $cond['request_edate']
        ];

        $cond['check_posting'] = Arr::get($query, 'check_posting', 'all');

        $dataList = RequestOrder::request_order_list(
            $cond['client'],
            $cond['request_sn'],
            $cond['source_sn'],
            $request_price,
            $request_posting_date,
            $cond['check_posting'],
        )->paginate($page)->appends($query);

        $user = User::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $customer = Customer::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $depot = Depot::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $supplier = Supplier::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $client_merged = array_merge($user, $customer, $depot, $supplier);

        $check_posting_status = [
            'all'=>'不限',
            '0'=>'未入款',
            '1'=>'已入款',
        ];

        return view('cms.account_management.request.list', [
            'data_per_page' => $page,
            'dataList' => $dataList,
            'cond' => $cond,
            'client' => $client_merged,
            'check_posting_status' => $check_posting_status,
        ]);
    }


    public function create(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'client_key' => 'required|string',
                'request_grade_id' => 'required|exists:acc_all_grades,id',
                'price' => 'required|numeric|between:0,9999999999.9999',
                'qty' => 'required|numeric|between:0,9999999999.9999',
                'summary' => 'nullable|string',
                'memo' => 'nullable|string',
            ]);

            $client_key = explode('|', request('client_key'));

            if(count($client_key) > 1){
                $client = User::where([
                        'id'=>$client_key[0],
                    ])
                    ->where('name', 'LIKE', "%{$client_key[1]}%")
                    ->select(
                        'id',
                        'name',
                        'email'
                    )
                    ->selectRaw('
                        IF(id IS NOT NULL, "", "") AS phone,
                        IF(id IS NOT NULL, "", "") AS address
                    ')
                    ->first();

                if(! $client){
                    $client = Customer::leftJoin('usr_customers_address AS customer_add', function ($join) {
                            $join->on('usr_customers.id', '=', 'customer_add.usr_customers_id_fk');
                            $join->where([
                                'customer_add.is_default_addr'=>1,
                            ]);
                        })->where([
                            'usr_customers.id'=>$client_key[0],
                        ])
                        ->where('usr_customers.name', 'LIKE', "%{$client_key[1]}%")
                        ->select(
                            'usr_customers.id',
                            'usr_customers.name',
                            'usr_customers.phone AS phone',
                            'usr_customers.email',
                            'customer_add.address AS address'
                        )->first();

                    if(! $client){
                        $client = Depot::where('id', '=', $client_key[0])
                            ->where('name', 'LIKE', "%{$client_key[1]}%")
                            ->select(
                                'depot.id',
                                'depot.name',
                                'depot.tel AS phone',
                                'depot.address AS address'
                            )->first();

                        if(! $client){
                            $client = Supplier::where([
                                'id'=>$client_key[0],
                            ])
                            ->where('name', 'LIKE', "%{$client_key[1]}%")
                            ->select(
                                'id',
                                'name',
                                'contact_tel AS phone',
                                'email',
                                'contact_address AS address'
                            )->first();
                        }
                    }
                }

                $parm = [
                    'price' =>request('price'),
                    'qty' =>request('qty'),
                    'total_price' =>request('price') * request('qty'),
                    'rate' =>request('rate'),
                    'currency_id' =>request('currency_id'),
                    'request_grade_id' =>request('request_grade_id'),
                    'summary' =>request('summary'),
                    'memo' =>request('memo'),
                    'client_id' =>$client->id,
                    'client_name' =>$client->name,
                    'client_phone' =>$client->phone,
                    'client_address' =>$client->address,
                    'creator_id' =>auth('user')->user() ? auth('user')->user()->id : null,
                ];
                $request_order = RequestOrder::create_request_order($parm);

                if($request_order){
                    wToast(__('請款單建立成功'));

                    return redirect()->route('cms.request.show', [
                        'id'=>$request_order->id,
                    ]);
                }
            }

            wToast(__('請款單建立失敗', ['type'=>'danger']));
            return redirect()->back();
        }

        $user = User::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $customer = Customer::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $depot = Depot::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $supplier = Supplier::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $client_merged = array_merge($user, $customer, $depot, $supplier);

        $total_grades = GeneralLedger::total_grade_list();

        $currency = DB::table('acc_currency')->get();

        return view('cms.account_management.request.edit', [
            'form_action'=>route('cms.request.create'),
            'client' => $client_merged,
            'total_grades' => $total_grades,
            'currency' => $currency,
        ]);
    }


    public function show(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id' => 'required|exists:acc_request_orders,id',
        ]);

        $request_order = RequestOrder::findOrFail($id);

        $applied_company = DB::table('acc_company')->where('id', 1)->first();

        $sales = User::find($request_order->creator_id);
        $accountant = User::find($request_order->accountant_id);

        $request_grade = AllGrade::find($request_order->request_grade_id)->eachGrade;

        $zh_price = num_to_str($request_order->total_price);

        return view('cms.account_management.request.show', [
            'request_order' => $request_order,
            'applied_company' => $applied_company,
            'sales' => $sales,
            'accountant' => $accountant,
            'request_grade' => $request_grade,
            'zh_price' => $zh_price,
        ]);
    }


    public function ro_edit(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id' => 'required|exists:acc_request_orders,id',
        ]);

        $request_order = RequestOrder::findOrFail($id);

        $request_grade = AllGrade::find($request_order->request_grade_id)->eachGrade;

        $received_order = ReceivedOrder::find($request_order->received_order_id);
        $received_data = ReceivedOrder::get_received_detail($request_order->received_order_id);

        $tw_price = $request_order->total_price - $received_data->sum('tw_price');

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

        return view('cms.account_management.request.ro_edit', [
            'breadcrumb_data' => ['id' => $request_order->id],
            'form_action' => route('cms.request.ro-store', ['id' => $request_order->id]),
            'previou_url' => route('cms.request.show', ['id' => $request_order->id]),
            'purchaser' => $request_order->client_name,
            'request_grade' => $request_grade,
            'request_order' => $request_order,

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
            'id' => 'required|exists:acc_request_orders,id',
            'acc_transact_type_fk' => 'required|string|in:' . implode(',', ReceivedMethod::asArray()),
            'tw_price' => 'required|numeric',
            request('acc_transact_type_fk') => 'required|array',
            request('acc_transact_type_fk') . '.grade' => 'required|exists:acc_all_grades,id',
            'summary'=>'nullable|string',
            'note'=>'nullable|string',
        ]);

        $data = $request->except('_token');

        $request_order = RequestOrder::findOrFail($id);

        $source_type = app(RequestOrder::class)->getTable();
        $received_order = ReceivedOrder::where([
            'source_type'=>$source_type,
            'source_id'=>$id,
        ])->first();

        if(!$received_order){
            $received_order = ReceivedOrder::create_received_order($source_type, $id, $request_order->total_price);

            $parm = [
                'id' => $id,
                'received_order_id' => $received_order->id,
            ];
            RequestOrder::update_request_order_approval($parm);
        }

        $received_order_id = $received_order->id;

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
            return redirect()->route('cms.request.ro-receipt', [
                'id' => $id,
            ]);

        } else {
            return redirect()->route('cms.request.ro-edit', [
                'id' => $id,
            ]);
        }
    }


    public function ro_receipt(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id'=>'required|exists:acc_request_orders,id',
        ]);

        $request_order = RequestOrder::findOrFail($id);

        $request_grade = AllGrade::find($request_order->request_grade_id)->eachGrade;

        $received_order = ReceivedOrder::findOrFail($request_order->received_order_id);
        $received_data = ReceivedOrder::get_received_detail($request_order->received_order_id);
        $data_status_check = ReceivedOrder::received_data_status_check($received_data);

        if (!$received_order->balance_date) {
            // return abort(404);

            return redirect()->route('cms.request.ro-edit', [
                'id' => $id,
            ]);
        }

        $purchaser = $request_order;
        $undertaker = User::find($received_order->usr_users_id);

        // $accountant = User::whereIn('id', $received_data->pluck('accountant_id_fk')->toArray())->get();
        // $accountant = array_unique($accountant->pluck('name')->toArray());
        // asort($accountant);
        $accountant = User::find($received_order->accountant_id) ? User::find($received_order->accountant_id)->name : null;

        $zh_price = num_to_str($received_order->price);
        $view = 'cms.account_management.request.ro_receipt';
        if (request('method') == 'print') {
            dd('cms.account_management.request.ro_receipt 付款單');
            $view = '';
        }

        return view($view, [
            'breadcrumb_data' => ['id' => $request_order->id],
            'request_grade' => $request_grade,
            'request_order' => $request_order,
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
            'id' => 'required|exists:acc_request_orders,id',
        ]);

        $request_order = RequestOrder::findOrFail($id);

        $received_order = ReceivedOrder::findOrFail($request_order->received_order_id);

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

                return redirect()->route('cms.request.ro-receipt', ['id'=>request('id')]);

            } catch (\Exception $e) {
                DB::rollback();
                wToast(__('入帳日期更新失敗', ['type'=>'danger']));

                return redirect()->back();
            }

        } else if($request->isMethod('get')){
            $received_data = ReceivedOrder::get_received_detail($request_order->received_order_id);
            $data_status_check = ReceivedOrder::received_data_status_check($received_data);

            if($received_order->receipt_date){
                if($data_status_check){
                    return redirect()->back();
                }

                $received_order->update([
                    'accountant_id'=>null,
                    'receipt_date'=>null,
                ]);

                wToast(__('入帳日期已取消'));
                return redirect()->route('cms.request.ro-receipt', ['id'=>request('id')]);

            } else {
                $undertaker = User::find($received_order->usr_users_id);

                $order_list_data = $request_order->get();

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
                    $name = $value->summary;

                    $tmp = [
                        'account_code'=>$account_code,
                        'name'=>$name,
                        'price'=>$value->total_price,
                        'type'=>'r',
                        'd_type'=>'product',

                        'account_name'=>$account_name,
                        'method_name'=>null,
                        'summary'=>$value->summary ?? null,
                        'note'=>$value->note ?? null,
                        'product_title'=>$value->summary,
                        'del_even'=>$value->del_even ?? null,
                        'del_category_name'=>$value->del_category_name ?? null,
                        'product_price'=>$value->price,
                        'product_qty'=>$value->qty,
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

                return view('cms.account_management.request.ro_review', [
                    'breadcrumb_data' => ['id' => $request_order->id],
                    'form_action' => route('cms.request.ro-review', ['id'=>request('id')]),
                    'previou_url' => route('cms.request.ro-receipt', ['id'=>request('id')]),
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
                ]);
            }
        }
    }


    public function ro_taxation(Request $request, $id)
    {
        $request->merge([
            'id' => $id,
        ]);
        $request->validate([
            'id' => 'required|exists:acc_request_orders,id',
        ]);

        $request_order = RequestOrder::findOrFail($id);

        $received_order = ReceivedOrder::findOrFail($request_order->received_order_id);

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
                        $value['id'] = $key;
                        RequestOrder::update_request_order($value);
                    }
                }

                DB::commit();
                wToast(__('摘要/稅別更新成功'));

            } catch (\Exception $e) {
                DB::rollback();
                wToast(__('摘要/稅別更新失敗', ['type'=>'danger']));
            }

            return redirect()->route('cms.request.ro-receipt', ['id'=>request('id')]);

        } else if($request->isMethod('get')){
            $order_list_data = $request_order->get();

            $received_data = ReceivedOrder::get_received_detail($request_order->received_order_id);

            $total_grades = GeneralLedger::total_grade_list();

            return view('cms.account_management.request.ro_taxation', [
                'breadcrumb_data' => ['id' => $request_order->id],
                'form_action' => route('cms.request.ro-taxation', ['id'=>request('id')]),
                'previou_url' => route('cms.request.ro-receipt', ['id'=>request('id')]),
                'received_order'=>$received_order,
                'order_list_data'=>$order_list_data,
                'received_data'=>$received_data,
                'total_grades'=>$total_grades,
            ]);
        }
    }
}
