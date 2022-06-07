<?php

namespace App\Http\Controllers\Cms\AccountManagement;



use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use App\Enums\Payable\PayableModelType;
use App\Enums\Supplier\Payment;

use App\Models\AllGrade;
use App\Models\AccountPayable;
use App\Models\Order;
use App\Models\PayableAccount;
use App\Models\PayableCash;
use App\Models\PayableCheque;
use App\Models\PayableForeignCurrency;
use App\Models\PayableOther;
use App\Models\PayableRemit;
use App\Models\PayingOrder;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\GeneralLedger;
use App\Models\PayableDefault;

class AccountPayableCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();
        $page = getPageCount(Arr::get($query, 'data_per_page', 10)) > 0 ? getPageCount(Arr::get($query, 'data_per_page', 10)) : 10;

        $cond = [];

        $cond['supplier_id'] = Arr::get($query, 'supplier_id', []);
        if (gettype($cond['supplier_id']) == 'string') {
            $cond['supplier_id'] = explode(',', $cond['supplier_id']);
        } else {
            $cond['supplier_id'] = [];
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
            $cond['supplier_id'],
            $cond['p_order_sn'],
            $cond['purchase_sn'],
            $p_order_price,
            $p_order_payment_date,
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

                    $name = $payment_method_name . ' ' . $pay_v->note . '（' . $account_code . ' - ' . $account_name . '）';

                    $tmp = [
                        'account_code'=>$account_code,
                        'name'=>$name,
                        'price'=>$pay_v->tw_price,
                        'type'=>'p',
                        'd_type'=>'payable',

                        'account_name'=>$account_name,
                        'method_name'=>$payment_method_name,
                        'note'=>$pay_v->note,
                        'product_title'=>null,
                        'del_even'=>null,
                        'del_category_name'=>null,
                        'product_price'=>null,
                        'product_qty'=>null,
                        'product_owner'=>null,
                        'discount_title'=>null,
                        'payable_type'=>$pay_v->payable_type,
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
                        'note'=>null,
                        'product_title'=>$pro_v->title,
                        'del_even'=>null,
                        'del_category_name'=>null,
                        'product_price'=>$avg_price,
                        'product_qty'=>$pro_v->num,
                        'product_owner'=>$pro_v->product_owner,
                        'discount_title'=>null,
                        'payable_type'=>null,
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
                    'note'=>null,
                    'product_title'=>null,
                    'del_even'=>null,
                    'del_category_name'=>null,
                    'product_price'=>null,
                    'product_qty'=>null,
                    'product_owner'=>null,
                    'discount_title'=>null,
                    'payable_type'=>null,
                ];
                GeneralLedger::classification_processing($debit, $credit, $tmp);
            }

            $value->debit = $debit;
            $value->credit = $credit;
        }
        // accounting classification end

        return view('cms.account_management.account_payable.list', [
            'data_per_page' => $page,
            'dataList' => $dataList,
            'cond' => $cond,
            'supplier' => Supplier::whereNull('deleted_at')->toBase()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->validate([
            'payOrdType' => 'required|regex:/^(pcs)$/',
            'payOrdId' => 'required|exists:pcs_paying_orders,id',
            'isFinalPay' => 'required|in:0,1',
            'purchaseId' => 'required|exists:pcs_purchase,id',
        ]);

        $payOrdId = $request['payOrdId'];

        // if ($request['type'] === 'pcs') {
        //     $type = 'App\Models\PayingOrder';
        // }

        $all_payable_type_data = [
            'payableCash' => [],
            'payableCheque' => [],
            'payableRemit' => [],
            'payableForeignCurrency' => [],
            'payableAccount' => [],
            'payableOther' => [],
        ];

        $pay_order = PayingOrder::where([
            'id'=>$payOrdId,
            'source_type'=>app(Purchase::class)->getTable(),
        ])->first();

        if(! $pay_order){
            return abort(404);
        }

        $product_grade_name = AllGrade::find($pay_order->product_grade_id)->eachGrade->code . ' - ' . AllGrade::find($pay_order->product_grade_id)->eachGrade->name;
        $logistics_grade_name = AllGrade::find($pay_order->logistics_grade_id)->eachGrade->code . ' - ' . AllGrade::find($pay_order->logistics_grade_id)->eachGrade->name;

        $purchase_item_data = PurchaseItem::getPurchaseItemsByPurchaseId($pay_order->source_id);
        $logistics = Purchase::findOrFail($pay_order->source_id);

        $deposit_payment_data = PayingOrder::getPayingOrdersWithPurchaseID($pay_order->source_id, 0)->first();

        $purchase_data = Purchase::getPurchase($pay_order->source_id)->first();
        $supplier = Supplier::where('id', '=', $purchase_data->supplier_id)->first();
        $currency = DB::table('acc_currency')->find($pay_order->acc_currency_fk);
        if(!$currency){
            $currency = (object)[
                'name'=>'NTD',
                'rate'=>1,
            ];
        }

        $paid_paying_order_data = PayingOrder::where(function ($q){
                $q->where([
                    'source_type'=>app(Purchase::class)->getTable(),
                    'source_id'=>request('purchaseId'),
                    'deleted_at'=>null,
                ]);

                if(request('isFinalPay') === '0'){
                    $q->where([
                        'type'=>request('isFinalPay'),
                    ]);
                }
            })->get();

        $payable_data = AccountPayable::whereIn('pay_order_id', $paid_paying_order_data->pluck('id')->toArray())->get();
        foreach($payable_data as $value){
            if($value->acc_income_type_fk == 4){
                $value->currency_name = DB::table('acc_currency')->find($value->payable->acc_currency_fk)->name;
                $value->currency_rate = $value->payable->rate;
            } else {
                $value->currency_name = 'NTD';
                $value->currency_rate = 1;
            }
        }
        $tw_price = $paid_paying_order_data->sum('price') - $payable_data->sum('tw_price');

        $total_grades = GeneralLedger::total_grade_list();

        return view('cms.account_management.account_payable.edit', [
            'tw_price' => $tw_price,
            'payable_data' => $payable_data,

            // 'thirdGradesDataList' => $thirdGradesDataList,
            // 'fourthGradesDataList' => $fourthGradesDataList,
            // 'currencyData' => $currencyData,
            // 'paymentStatusList' => $payStatusArray,
            'cashDefault' => PayableDefault::where('name', 'cash')->pluck('default_grade_id')->toArray(),
            'chequeDefault' => PayableDefault::where('name', 'cheque')->pluck('default_grade_id')->toArray(),
            'remitDefault' => PayableDefault::where('name', 'remittance')->pluck('default_grade_id')->toArray(),
            'all_currency' => PayableDefault::getCurrencyOptionData()['selectedCurrencyResult']->toArray(),
            'currencyDefault' => PayableDefault::where('name', 'foreign_currency')->pluck('default_grade_id')->toArray(),
            'accountPayableDefault' => PayableDefault::where('name', 'accounts_payable')->pluck('default_grade_id')->toArray(),
            'otherDefault' => PayableDefault::where('name', 'other')->pluck('default_grade_id')->toArray(),

            'method' => 'create',
            'transactTypeList' => AccountPayable::getTransactTypeList(),
            'chequeStatus' => AccountPayable::getChequeStatus(),
            'formAction' => Route('cms.ap.store'),

            'breadcrumb_data' => ['id' => $pay_order->source_id, 'sn' => $purchase_data->purchase_sn, 'type' => request('isFinalPay')],
            'product_grade_name' => $product_grade_name,
            'logistics_grade_name' => $logistics_grade_name,
            'logistics_price' => $logistics->logistics_price,
            'purchase_item_data' => $purchase_item_data,
            'deposit_payment_data' => $deposit_payment_data,
            'pay_order' => $pay_order,
            'currency' => $currency,
            'type' => request('isFinalPay') === '0' ? 'deposit' : 'final',
            'all_payable_type_data' => $all_payable_type_data,
            'purchase_data' => $purchase_data,
            'supplier' => $supplier,

            'total_grades' => $total_grades,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'acc_transact_type_fk'    => ['required', 'string', 'regex:/^[1-6]$/'],
            'pay_order_type' => ['required', 'string', 'regex:/^(pcs)$/'],
            'pay_order_id' => 'required|exists:pcs_paying_orders,id',
            'is_final_payment' => ['required', 'int', 'regex:/^(0|1)$/']
        ]);
        $req = $request->all();
        $payableType = $req['acc_transact_type_fk'];

        switch ($payableType) {
            case Payment::Cash:
                PayableCash::storePayableCash($req);
                break;
            case Payment::Cheque:
                PayableCheque::storePayableCheque($req);
                break;
            case Payment::Remittance:
                PayableRemit::storePayableRemit($req);
                break;
            case Payment::ForeignCurrency:
                PayableForeignCurrency::storePayableCurrency($req);
                break;
            case Payment::AccountsPayable:
                PayableAccount::storePayablePayableAccount($req);
                break;
            case Payment::Other:
                PayableOther::storePayableOther($req);
                break;
        }

        $pay_order = PayingOrder::find(request('pay_order_id'));
        $pay_list = AccountPayable::where('pay_order_id', request('pay_order_id'))->get();
        if (count($pay_list) > 0 && $pay_order->price == $pay_list->sum('tw_price')) {
            $pay_order->update([
                'balance_date'=>date("Y-m-d H:i:s"),
            ]);
        }

        if (PayingOrder::find(request('pay_order_id')) && PayingOrder::find(request('pay_order_id'))->balance_date) {
            return redirect()->route('cms.purchase.view-pay-order', [
                'id' => $req['purchase_id'],
                'type' => $req['is_final_payment']
            ]);

        } else {
            return redirect()->route('cms.ap.create', [
                'payOrdId' => request('pay_order_id'),
                'payOrdType' => 'pcs',
                'isFinalPay' => request('is_final_payment'),
                'purchaseId' => $pay_order->source_id
            ]);
        }
    }


    public function logistics_create(Request $request, $id, $sid)
    {
        $request->merge([
            'id'=>$id,
            'sid'=>$sid,
        ]);

        $request->validate([
            'id' => 'required|exists:ord_orders,id',
            'sid' => 'required|exists:ord_sub_orders,id',
        ]);

        $source_type = app(Order::class)->getTable();
        $type = 1;

        $paying_order = PayingOrder::where([
            'source_type'=>$source_type,
            'source_id'=>$id,
            'source_sub_id'=>$sid,
            'type'=>$type,
            'deleted_at'=>null,
        ])->first();

        if($request->isMethod('post')){
            if(! $paying_order) {
                return abort(404);
            }

            $request->merge([
                'pay_order_id'=>$paying_order->id,
            ]);

            $request->validate([
                'acc_transact_type_fk' => 'required|regex:/^[1-6]$/',
            ]);

            $req = $request->all();

            $payable_type = $req['acc_transact_type_fk'];

            switch ($payable_type) {
                case Payment::Cash:
                    PayableCash::storePayableCash($req);
                    break;
                case Payment::Cheque:
                    PayableCheque::storePayableCheque($req);
                    break;
                case Payment::Remittance:
                    PayableRemit::storePayableRemit($req);
                    break;
                case Payment::ForeignCurrency:
                    PayableForeignCurrency::storePayableCurrency($req);
                    break;
                case Payment::AccountsPayable:
                    PayableAccount::storePayablePayableAccount($req);
                    break;
                case Payment::Other:
                    PayableOther::storePayableOther($req);
                    break;
            }

            $payable_data = AccountPayable::whereIn('pay_order_id', [$paying_order->id])->get();
            if (count($payable_data) > 0 && $paying_order->price == $payable_data->sum('tw_price')) {
                $paying_order->update([
                    'balance_date'=>date("Y-m-d H:i:s"),
                ]);
            }

            if (PayingOrder::find($paying_order->id) && PayingOrder::find($paying_order->id)->balance_date) {
                return redirect()->route('cms.order.detail', [
                    'id' => $id,
                    'subOrderId' => $sid,
                ]);

            } else {
                return redirect()->route('cms.ap.logistics-create', [
                    'id' => $id,
                    'sid' => $sid,
                ]);
            }

        } else {

            if(! $paying_order || $paying_order->balance_date) {
                return abort(404);
            }

            $order = Order::orderDetail($id)->get()->first();
            $sub_order = Order::subOrderDetail($id, $sid, true)->get()->toArray()[0];

            $supplier = Supplier::find($sub_order->supplier_id);

            $logistics_grade = AllGrade::find($paying_order->logistics_grade_id)->eachGrade->code . ' - ' . AllGrade::find($paying_order->logistics_grade_id)->eachGrade->name;

            $currency = DB::table('acc_currency')->find($paying_order->acc_currency_fk);
            if(!$currency){
                $currency = (object)[
                    'name'=>'NTD',
                    'rate'=>1,
                ];
            }

            $payable_data = AccountPayable::whereIn('pay_order_id', [$paying_order->id])->get();

            foreach($payable_data as $value){
                if($value->acc_income_type_fk == 4){
                    $value->currency_name = DB::table('acc_currency')->find($value->payable->acc_currency_fk)->name;
                    $value->currency_rate = $value->payable->rate;
                } else {
                    $value->currency_name = 'NTD';
                    $value->currency_rate = 1;
                }
            }

            $tw_price = $paying_order->price - $payable_data->sum('tw_price');

            $total_grades = GeneralLedger::total_grade_list();

            return view('cms.account_management.account_payable.logistics_edit', [
                'breadcrumb_data' => ['id' => $id, 'sid' => $sid, 'sn' => $order->sn],
                'paying_order' => $paying_order,
                'order' => $order,
                'sub_order' => $sub_order,
                'supplier' => $supplier,
                'logistics_grade' => $logistics_grade,
                'currency' => $currency,
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

                'form_action' => Route('cms.ap.logistics-create', ['id' => $id, 'sid' => $sid]),
                'method' => 'create',
                'transactTypeList' => AccountPayable::getTransactTypeList(),
                'chequeStatus' => AccountPayable::getChequeStatus(),
            ]);
        }
    }
}
