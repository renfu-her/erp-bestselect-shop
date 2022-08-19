<?php

namespace App\Http\Controllers\Cms\AccountManagement;



use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use App\Enums\Supplier\Payment;
use App\Enums\Payable\ChequeStatus;

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
use App\Models\User;

class AccountsPayableCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();
        $page = getPageCount(Arr::get($query, 'data_per_page', 100)) > 0 ? getPageCount(Arr::get($query, 'data_per_page', 100)) : 100;

        $cond = [];

        $cond['accounts_payable_grade_id'] = Arr::get($query, 'accounts_payable_grade_id', []);
        if (gettype($cond['accounts_payable_grade_id']) == 'string') {
            $cond['accounts_payable_grade_id'] = explode(',', $cond['accounts_payable_grade_id']);
        } else {
            $cond['accounts_payable_grade_id'] = [];
        }

        // $cond['authamt_min_price'] = Arr::get($query, 'authamt_min_price', null);
        // $cond['authamt_max_price'] = Arr::get($query, 'authamt_max_price', null);
        // $authamt_price = [
        //     $cond['authamt_min_price'],
        //     $cond['authamt_max_price']
        // ];
        $authamt_price = null;

        $cond['po_created_sdate'] = Arr::get($query, 'po_created_sdate', null);
        $cond['po_created_edate'] = Arr::get($query, 'po_created_edate', null);
        $po_created_date = [
            $cond['po_created_sdate'],
            $cond['po_created_edate']
        ];

        $cond['status_code'] = Arr::get($query, 'status_code', null);

        $data_list = PayingOrder::get_accounts_payable_list(
                null,
                $cond['status_code'],
                null,
                $cond['accounts_payable_grade_id'],
                $authamt_price,
                $po_created_date
            )->paginate($page)->appends($query);

        $accounts_payable_grade = PayableDefault::leftJoinSub(GeneralLedger::getAllGrade(), 'grade', function($join) {
                $join->on('grade.primary_id', 'acc_payable_default.default_grade_id');
            })
            ->select(
                'acc_payable_default.name',
                'grade.primary_id as grade_id',
                'grade.code as grade_code',
                'grade.name as grade_name'
            )
            ->where('acc_payable_default.name', 'accounts_payable')
            ->get();

        return view('cms.account_management.accounts_payable.list', [
            'data_per_page' => $page,
            'data_list' => $data_list,
            'cond' => $cond,
            'accounts_payable_grade' => $accounts_payable_grade,
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
        $po_target = $type == 't' ? [$id, $key] : null;

        if($request->isMethod('post')){
            $request->validate([
                'selected' => 'required|array',
                'selected.*' => 'exists:acc_payable_account,id',
                'accounts_payable_id' => 'required|array',
                'accounts_payable_id.*' => 'exists:acc_payable_account,id',
                'amt_net' => 'required|array',
                'amt_net.*' => 'required|numeric|between:0,9999999999.99',
            ]);

            $compare = array_diff(request('selected'), request('accounts_payable_id'));
            if(count($compare) == 0){
                $source_type = app(PayingOrder::class)->getTable();
                // $n_id = DB::select("SHOW TABLE STATUS FROM 'shop-dev' LIKE '" . $source_type . "'")[0]->Auto_increment;
                $n_id = PayingOrder::get()->count() + 1;
                $accounts_payable_id = current(request('accounts_payable_id'));
                $payable = DB::table('acc_payable')->where([
                    'acc_income_type_fk'=>5,
                    'payable_id'=>$accounts_payable_id
                ])->first();

                $pre_paying_order = PayingOrder::find($payable->pay_order_id);
                $product_grade = PayableDefault::where('name', '=', 'product')->first()->default_grade_id;
                $logistics_grade = PayableDefault::where('name', '=', 'logistics')->first()->default_grade_id;

                $result = PayingOrder::createPayingOrder(
                    $source_type,
                    $n_id,
                    null,
                    $request->user()->id,
                    1,
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
                    'accounts_payable_id'=>request('accounts_payable_id'),
                    'status_code'=>0,
                    'append_pay_order_id'=>$paying_order->id,
                    'sn'=>$paying_order->sn,
                    'amt_net'=>request('amt_net'),
                ];
                PayingOrder::update_account_payable_method($parm);

                return redirect()->route('cms.accounts_payable.po-edit', [
                    'id'=>$paying_order->id,
                ]);
            }

            wToast(__('應付帳款付款單建立失敗', ['type'=>'danger']));
            return redirect()->back();
        }

        $data_list = PayingOrder::get_accounts_payable_list(null, 0, null, $grade_id, null, null, $po_target)->get();

        return view('cms.account_management.accounts_payable.claim', [
            'form_action'=>route('cms.accounts_payable.claim', ['type'=>$type, 'id'=>$id, 'key'=>$key]),
            'data_list'=>$data_list,
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

        $accounts_payable_id = DB::table('acc_payable_account')->where('append_pay_order_id', $id)->pluck('id')->toArray();
        $target_items = PayingOrder::get_accounts_payable_list($accounts_payable_id, 0)->get();

        $paying_order = PayingOrder::findOrFail($id);
        $payable_data = PayingOrder::get_payable_detail($id);

        $tw_price = $paying_order->price - $payable_data->sum('tw_price');

        $total_grades = GeneralLedger::total_grade_list();

        return view('cms.account_management.accounts_payable.po_edit', [
            'breadcrumb_data' => ['id' => $id],
            'form_action' => route('cms.accounts_payable.po-store', ['id' => $paying_order->id]),
            'previous_url' => route('cms.accounts_payable.index'),
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
                'balance_date'=>date('Y-m-d H:i:s'),
                'payment_date' => $data['payment_date'],
            ]);
        }

        if (PayingOrder::find($paying_order->id) && PayingOrder::find($paying_order->id)->balance_date) {
            $accounts_payable_id = DB::table('acc_payable_account')->where('append_pay_order_id', $id)->pluck('id')->toArray();

            $parm = [
                'accounts_payable_id'=>$accounts_payable_id,
                'status_code'=>1,
                'append_pay_order_id'=>$paying_order->id,
                'sn'=>$paying_order->sn,
            ];
            PayingOrder::update_account_payable_method($parm);

            return redirect()->route('cms.accounts_payable.po-show', [
                'id' => $id,
            ]);

        } else {
            return redirect()->route('cms.accounts_payable.po-edit', [
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

        $accounts_payable_id = DB::table('acc_payable_account')->where('append_pay_order_id', $id)->pluck('id')->toArray();
        $target_items = PayingOrder::get_accounts_payable_list($accounts_payable_id, 1)->get();

        $paying_order = PayingOrder::findOrFail($id);
        $payable_data = PayingOrder::get_payable_detail($id);

        $zh_price = num_to_str($paying_order->price);

        if (!$paying_order->balance_date) {
            // return abort(404);

            return redirect()->route('cms.accounts_payable.po-edit', [
                'id' => $id,
            ]);
        }

        $undertaker = User::find($paying_order->usr_users_id);

        $accountant = User::whereIn('id', $payable_data->pluck('accountant_id_fk')->toArray())->get();
        $accountant = array_unique($accountant->pluck('name')->toArray());
        asort($accountant);

        $view = 'cms.account_management.accounts_payable.po_show';
        if (request('method') == 'print') {
            $view = 'doc.print_accounts_payable_delivery_pay';
        }

        return view($view, [
            'breadcrumb_data' => ['id' => $paying_order->id],
            'applied_company' => $applied_company,
            'paying_order' => $paying_order,
            'target_items' => $target_items,
            'payable_data' => $payable_data,
            'zh_price' => $zh_price,
            'undertaker'=>$undertaker,
            'accountant'=>implode(',', $accountant),
        ]);
    }
}
