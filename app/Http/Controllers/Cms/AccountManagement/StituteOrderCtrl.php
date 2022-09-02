<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Enums\Supplier\Payment;
use App\Enums\Payable\ChequeStatus;

use App\Models\AllGrade;
use App\Models\AccountPayable;
use App\Models\Customer;
use App\Models\DayEnd;
use App\Models\Depot;
use App\Models\GeneralLedger;
use App\Models\PayableDefault;
use App\Models\PayingOrder;
use App\Models\PayableAccount;
use App\Models\PayableCash;
use App\Models\PayableCheque;
use App\Models\PayableForeignCurrency;
use App\Models\PayableOther;
use App\Models\PayableRemit;
use App\Models\StituteOrder;
use App\Models\Supplier;
use App\Models\User;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class StituteOrderCtrl extends Controller
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

        $cond['so_sn'] = Arr::get($query, 'so_sn', null);
        $cond['source_sn'] = Arr::get($query, 'source_sn', null);

        $cond['stitute_min_price'] = Arr::get($query, 'stitute_min_price', null);
        $cond['stitute_max_price'] = Arr::get($query, 'stitute_max_price', null);
        $stitute_price = [
            $cond['stitute_min_price'],
            $cond['stitute_max_price']
        ];

        $cond['stitute_sdate'] = Arr::get($query, 'stitute_sdate', null);
        $cond['stitute_edate'] = Arr::get($query, 'stitute_edate', null);
        $stitute_payment_date = [
            $cond['stitute_sdate'],
            $cond['stitute_edate']
        ];

        $cond['check_payment'] = Arr::get($query, 'check_payment', 'all');

        $dataList = StituteOrder::stitute_order_list(
            $cond['client'],
            $cond['so_sn'],
            $cond['source_sn'],
            $stitute_price,
            $stitute_payment_date,
            $cond['check_payment'],
        )->paginate($page)->appends($query);

        $user = User::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $customer = Customer::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $depot = Depot::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $supplier = Supplier::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $client_merged = array_merge($user, $customer, $depot, $supplier);

        $check_payment_status = [
            'all'=>'不限',
            '0'=>'未入款',
            '1'=>'已入款',
        ];

        return view('cms.account_management.stitute.list', [
            'data_per_page' => $page,
            'dataList' => $dataList,
            'cond' => $cond,
            'client' => $client_merged,
            'check_payment_status' => $check_payment_status,
        ]);
    }


    public function create(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'client_key' => 'required|string',
                'stitute_grade_id' => 'required|exists:acc_all_grades,id',
                'price' => 'required|numeric|between:0,9999999999.9999',
                'qty' => 'required|numeric|between:0,9999999999.9999',
                'summary' => 'nullable|string',
                'memo' => 'nullable|string',
            ]);

            $client_key = explode('|', request('client_key'));

            if(count($client_key) > 1){
                $client = PayingOrder::payee($client_key[0], $client_key[1]);

                $parm = [
                    'price' =>request('price'),
                    'qty' =>request('qty'),
                    'total_price' =>request('price') * request('qty'),
                    'rate' =>request('rate'),
                    'currency_id' =>request('currency_id'),
                    'stitute_grade_id' =>request('stitute_grade_id'),
                    'summary' =>request('summary'),
                    'memo' =>request('memo'),
                    'client_id' =>$client->id,
                    'client_name' =>$client->name,
                    'client_phone' =>$client->phone,
                    'client_address' =>$client->address,
                    'creator_id' =>auth('user')->user() ? auth('user')->user()->id : null,
                ];
                $stitute_order = StituteOrder::create_stitute_order($parm);

                if($stitute_order){
                    wToast(__('代墊單建立成功'));

                    return redirect()->route('cms.stitute.show', [
                        'id'=>$stitute_order->id,
                    ]);
                }
            }

            wToast(__('代墊單建立失敗', ['type'=>'danger']));
            return redirect()->back();
        }

        $user = User::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $customer = Customer::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $depot = Depot::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $supplier = Supplier::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $client_merged = array_merge($user, $customer, $depot, $supplier);

        $total_grades = GeneralLedger::total_grade_list();

        $currency = DB::table('acc_currency')->get();

        return view('cms.account_management.stitute.create', [
            'form_action'=>route('cms.stitute.create'),
            'client' => $client_merged,
            'total_grades' => $total_grades,
            'currency' => $currency,
        ]);
    }


    public function edit(Request $request, $id)
    {
        $stitute_order = StituteOrder::findOrFail($id);

        if($request->isMethod('post')){
            $request->validate([
                'client_key' => 'required|string',
            ]);

            $client_key = explode('|', request('client_key'));

            if(count($client_key) > 1){
                $client = PayingOrder::payee($client_key[0], $client_key[1]);

                $stitute_order->update([
                    'client_id' =>$client->id,
                    'client_name' =>$client->name,
                    'client_phone' =>$client->phone,
                    'client_address' =>$client->address,
                ]);

                wToast(__('代墊單更新成功'));

                return redirect()->route('cms.stitute.show', [
                    'id'=>$stitute_order->id,
                ]);
            }

            wToast(__('代墊單更新失敗', ['type'=>'danger']));
            return redirect()->back();
        }

        $user = User::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $customer = Customer::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $depot = Depot::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $supplier = Supplier::whereNull('deleted_at')->select('id', 'name')->get()->toArray();
        $client_merged = array_merge($user, $customer, $depot, $supplier);

        return view('cms.account_management.stitute.edit', [
            'form_action'=>route('cms.stitute.edit', ['id'=>$id]),
            'client' => $client_merged,
            'stitute_order' => $stitute_order,
        ]);
    }


    public function show(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id' => 'required|exists:acc_stitute_orders,id',
        ]);

        $stitute_order = StituteOrder::findOrFail($id);

        $applied_company = DB::table('acc_company')->where('id', 1)->first();

        $sales = User::find($stitute_order->creator_id);
        $accountant = User::find($stitute_order->accountant_id);

        $stitute_grade = AllGrade::find($stitute_order->stitute_grade_id)->eachGrade;

        $zh_price = num_to_str($stitute_order->total_price);

        return view('cms.account_management.stitute.show', [
            'stitute_order' => $stitute_order,
            'applied_company' => $applied_company,
            'sales' => $sales,
            'accountant' => $accountant,
            'stitute_grade' => $stitute_grade,
            'zh_price' => $zh_price,
        ]);
    }


    public function po_edit(Request $request, $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id' => 'required|exists:acc_stitute_orders,id',
        ]);

        $stitute_order = StituteOrder::findOrFail($id);
        $stitute_grade = AllGrade::find($stitute_order->stitute_grade_id)->eachGrade;
        $currency = DB::table('acc_currency')->find($stitute_order->currency_id);
        if(!$currency){
            $currency = (object)[
                'name'=>'NTD',
                'rate'=>1,
            ];
        }

        $paying_order = PayingOrder::find($stitute_order->pay_order_id);
        $payable_data = PayingOrder::get_payable_detail($stitute_order->pay_order_id);

        $tw_price = $stitute_order->total_price - $payable_data->sum('tw_price');

        $total_grades = GeneralLedger::total_grade_list();

        return view('cms.account_management.stitute.po_edit', [
            'breadcrumb_data' => ['id' => $stitute_order->id],
            'form_action' => route('cms.stitute.po-store', ['id' => $stitute_order->id]),
            'previous_url' => route('cms.stitute.show', ['id' => $stitute_order->id]),
            'stitute_order' => $stitute_order,
            'stitute_grade' => $stitute_grade,
            'currency' => $currency,
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
            'id' => 'required|exists:acc_stitute_orders,id',
            'acc_transact_type_fk' => 'required|regex:/^[1-6]$/',
            'tw_price' => 'required|numeric',
            'summary'=>'nullable|string',
            'note'=>'nullable|string',
        ]);

        $stitute_order = StituteOrder::findOrFail($id);

        $source_type = app(StituteOrder::class)->getTable();
        $paying_order = PayingOrder::where([
            'source_type'=>$source_type,
            'source_id'=>$id,
        ])->first();

        if (!$paying_order) {
            $product_grade = PayableDefault::where('name', '=', 'product')->first()->default_grade_id;
            $logistics_grade = PayableDefault::where('name', '=', 'logistics')->first()->default_grade_id;

            $result = PayingOrder::createPayingOrder(
                $source_type,
                $id,
                null,
                $request->user()->id,
                1,
                $product_grade,
                $logistics_grade,
                $stitute_order->total_price,
                '',
                '',
                $stitute_order->client_id,
                $stitute_order->client_name,
                $stitute_order->client_phone,
                $stitute_order->client_address
            );

            $paying_order = PayingOrder::find($result['id']);

            $parm = [
                'id' => $id,
                'pay_order_id' => $result['id'],
            ];
            StituteOrder::update_stitute_order_approval($parm);
        }

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

        $payable_data = PayingOrder::get_payable_detail($paying_order->id);
        if (count($payable_data) > 0 && $paying_order->price == $payable_data->sum('tw_price')) {
            $paying_order->update([
                'balance_date' => date('Y-m-d H:i:s'),
                'payment_date' => $data['payment_date'],
            ]);

            DayEnd::match_day_end_status($data['payment_date'], $paying_order->sn);
        }

        if (PayingOrder::find($paying_order->id) && PayingOrder::find($paying_order->id)->balance_date) {
            return redirect()->route('cms.stitute.po-show', [
                'id' => $id,
            ]);

        } else {
            return redirect()->route('cms.stitute.po-edit', [
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
            'id'=>'required|exists:acc_stitute_orders,id',
        ]);

        $applied_company = DB::table('acc_company')->where('id', 1)->first();

        $stitute_order = StituteOrder::findOrFail($id);
        $stitute_grade = AllGrade::find($stitute_order->stitute_grade_id)->eachGrade;

        $zh_price = num_to_str($stitute_order->total_price);

        $paying_order = PayingOrder::findOrFail($stitute_order->pay_order_id);
        $payable_data = PayingOrder::get_payable_detail($stitute_order->pay_order_id);
        $data_status_check = PayingOrder::payable_data_status_check($payable_data);

        if (!$paying_order->balance_date) {
            // return abort(404);

            return redirect()->route('cms.stitute.po-edit', [
                'id' => $id,
            ]);
        }

        $undertaker = User::find($stitute_order->creator_id);

        $accountant = User::whereIn('id', $payable_data->pluck('accountant_id_fk')->toArray())->get();
        $accountant = array_unique($accountant->pluck('name')->toArray());
        asort($accountant);

        $view = 'cms.account_management.stitute.po_show';
        if (request('action') == 'print') {
            $view = 'doc.print_account_management_stitute_pay';
        }

        return view($view, [
            'breadcrumb_data' => ['id' => $stitute_order->id],
            'applied_company' => $applied_company,
            'stitute_order' => $stitute_order,
            'stitute_grade' => $stitute_grade,
            'zh_price' => $zh_price,
            'paying_order' => $paying_order,
            'payable_data' => $payable_data,
            'data_status_check' => $data_status_check,
            'undertaker'=>$undertaker,
            'accountant'=>implode(',', $accountant),
        ]);
    }
}
