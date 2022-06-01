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

            // 商品
            $product_account = AllGrade::find($value->po_product_grade_id) ? AllGrade::find($value->po_product_grade_id)->eachGrade : null;
            $account_code = $product_account ? $product_account->code : '1000';
            $account_name = $product_account ? $product_account->name : '無設定會計科目';
            $product_name = $account_code . ' - ' . $account_name;
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

            // 物流
            if($value->purchase_logistics_price <> 0){
                $log_account = AllGrade::find($value->po_logistics_grade_id) ? AllGrade::find($value->po_logistics_grade_id)->eachGrade : null;
                $account_code = $log_account ? $log_account->code : '5000';
                $account_name = $log_account ? $log_account->name : '無設定會計科目';
                $name = $account_code . ' - ' . $account_name;

                $tmp = [
                    'account_code'=>$account_code,
                    'name'=>$name,
                    'price'=>$value->purchase_logistics_price,
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

        $pay_order = PayingOrder::find($payOrdId);
        // $thirdGradesDataList = PayableDefault::getOptionDataByGrade(3);
        // $fourthGradesDataList = PayableDefault::getOptionDataByGrade(4);
        // $currencyData = PayableDefault::getCurrencyOptionData();

        // $payStatusArray = [
        //     [
        //         'id' => PayableStatus::Unpaid,
        //         'payment_status' => PayableStatus::getDescription(PayableStatus::Unpaid)
        //     ],
        //     [
        //         'id' => PayableStatus::Paid,
        //         'payment_status' => PayableStatus::getDescription(PayableStatus::Paid)
        //     ]
        // ];

        $product_grade_name = AllGrade::find($pay_order->product_grade_id)->eachGrade->code . ' - ' . AllGrade::find($pay_order->product_grade_id)->eachGrade->name;
        $logistics_grade_name = AllGrade::find($pay_order->logistics_grade_id)->eachGrade->code . ' - ' . AllGrade::find($pay_order->logistics_grade_id)->eachGrade->name;


        $purchase_item_data = PurchaseItem::getPurchaseItemsByPurchaseId($pay_order->purchase_id);
        $logistics = Purchase::findOrFail($pay_order->purchase_id);

        $deposit_payment_data = PayingOrder::getPayingOrdersWithPurchaseID($pay_order->purchase_id, 0)->first();

        $purchase_data = Purchase::getPurchase($pay_order->purchase_id)->first();
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
                    'purchase_id'=>request('purchaseId'),
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

            'breadcrumb_data' => ['id' => $pay_order->purchase_id, 'sn' => $purchase_data->purchase_sn, 'type' => request('isFinalPay')],
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
                'purchaseId' => $pay_order->purchase_id
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AccountPayable  $accountPayable
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AccountPayable  $accountPayable
     * @param  Request  $request
     * @param  int  $id primary ID of AccountPayable
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, int $id)
    {
        $request->merge([
            'id'=>$id,
        ]);

        $request->validate([
            'id' => 'required|exists:acc_payable,id',
            'payOrdType' => 'required|regex:/^(pcs)$/',
            'payOrdId' => 'required|exists:pcs_paying_orders,id',
            'isFinalPay' => 'required|in:0,1',
            'purchaseId' => 'required|exists:pcs_purchase,id',
        ]);

        $payOrdId = $request['payOrdId'];

        $payable_data = AccountPayable::find($id);
        $pay_method = $payable_data->acc_income_type_fk;

        $payableTypeData = $payable_data->payable;
        $grade = $payableTypeData->grade;

        switch ($pay_method) {
            case Payment::Cash:
                $payableCash = [
                    'grade_id_fk' => $grade->id,
                    'code' => $grade->code,
                    'name' => $grade->name,
                ];
                break;
            case Payment::Cheque:
                $payableCheque = [
                    'grade_id_fk' => $grade->id,
                    'code' => $grade->code,
                    'name' => $grade->name,
                    'check_num' => $payableTypeData->check_num,
                    'grade_type' => $payableTypeData->grade_type,
                    'maturity_date' => explode(' ', $payableTypeData->maturity_date)[0],
                    'cash_cheque_date' => explode(' ', $payableTypeData->cash_cheque_date)[0],
                    'cheque_status' => $payableTypeData->cheque_status,
                ];
                break;
            case Payment::Remittance:
                $payableRemit = [
                    'grade_id_fk' => $grade->id,
                    'code' => $grade->code,
                    'name' => $grade->name,
                    'remit_date' => explode(' ', $payableTypeData->remit_date)[0],
                ];
                break;
            case Payment::ForeignCurrency:
                $payableForeignCurrency = [
                    'grade_id_fk' => $grade->id,
                    'code' => $grade->code,
                    'name' => $grade->name,
                    'foreign_currency' => $payableTypeData->foreign_currency,
                    'rate' => $payableTypeData->rate,
                    'acc_currency_fk' => $payableTypeData->acc_currency_fk,
                ];
                break;
            case Payment::AccountsPayable:
                $payableAccount = [
                    'grade_id_fk' => $grade->id,
                    'code' => $grade->code,
                    'name' => $grade->name,
                ];
                break;
            case Payment::Other:
                $payableOther = [
                    'grade_id_fk' => $grade->id,
                    'code' => $grade->code,
                    'name' => $grade->name,
                ];
                break;
        }
        $all_payable_type_data = [
            'payableCash' => $payableCash ?? [],
            'payableCheque' => $payableCheque ?? [],
            'payableRemit' => $payableRemit ?? [],
            'payableForeignCurrency' => $payableForeignCurrency ?? [],
            'payableAccount' => $payableAccount ?? [],
            'payableOther' => $payableOther ?? [],
        ];

        $pay_order = PayingOrder::find($payOrdId);
        // $thirdGradesDataList = PayableDefault::getOptionDataByGrade(3);
        // $fourthGradesDataList = PayableDefault::getOptionDataByGrade(4);
        // $currencyData = PayableDefault::getCurrencyOptionData();

        // $payStatusArray = [
        //     [
        //         'id' => PayableStatus::Unpaid,
        //         'payment_status' => PayableStatus::getDescription(PayableStatus::Unpaid)
        //     ],
        //     [
        //         'id' => PayableStatus::Paid,
        //         'payment_status' => PayableStatus::getDescription(PayableStatus::Paid)
        //     ]
        // ];

        $product_grade_name = AllGrade::find($pay_order->product_grade_id)->eachGrade->code . ' - ' . AllGrade::find($pay_order->product_grade_id)->eachGrade->name;
        $logistics_grade_name = AllGrade::find($pay_order->logistics_grade_id)->eachGrade->code . ' - ' . AllGrade::find($pay_order->logistics_grade_id)->eachGrade->name;


        $deposit_payment_price = 0;
        $total_price = 0;

        $purchase_item_data = PurchaseItem::getPurchaseItemsByPurchaseId($pay_order->purchase_id);
        $logistics = Purchase::findOrFail($pay_order->purchase_id);

        $deposit_payment_data = PayingOrder::getPayingOrdersWithPurchaseID($pay_order->purchase_id, 0)->first();

        $purchase_data = Purchase::getPurchase($pay_order->purchase_id)->first();
        $supplier = Supplier::where('id', '=', $purchase_data->supplier_id)->first();
        $currency = DB::table('acc_currency')->find($pay_order->acc_currency_fk);
        if(!$currency){
            $currency = (object)[
                'name'=>'NTD',
                'rate'=>1,
            ];
        }

        $paid_paying_order_data = PayingOrder::where([
                'purchase_id'=>$pay_order->purchase_id,
                'deleted_at'=>null,
            ])->get();
        $payable_data = AccountPayable::whereIn('pay_order_id', $paid_paying_order_data->pluck('id')->toArray())->get();
        $tw_price = $paid_paying_order_data->sum('price') - $payable_data->sum('tw_price');

        return view('cms.account_management.account_payable.edit', [
            'tw_price' => $tw_price,
            'payable_data' => $payable_data,
            'all_payable_type_data' => $all_payable_type_data,
            'payment_date' => explode(' ', $payable_data->payment_date)[0],
            // 'note' => $payable_data->note ?? '',
            // 'thirdGradesDataList' => $thirdGradesDataList,
            // 'fourthGradesDataList' => $fourthGradesDataList,
            // 'currencyData' => $currencyData,
            // 'paymentStatusList' => $payStatusArray,

            'cashDefault' => PayableDefault::where('name', 'cash')->pluck('default_grade_id')->toArray(),
            'chequeDefault' => PayableDefault::where('name', 'cheque')->pluck('default_grade_id')->toArray(),
            'remitDefault' => PayableDefault::where('name', 'remittance')->pluck('default_grade_id')->toArray(),
            'currencyDefault' => PayableDefault::where('name', 'foreign_currency')->pluck('default_grade_id')->toArray(),
            'accountPayableDefault' => PayableDefault::where('name', 'accounts_payable')->pluck('default_grade_id')->toArray(),
            'otherDefault' => PayableDefault::where('name', 'other')->pluck('default_grade_id')->toArray(),
            'method' => 'edit',
            'transactTypeList' => AccountPayable::getTransactTypeList(),
            'chequeStatus' => AccountPayable::getChequeStatus(),
            'formAction' => Route('cms.ap.update', ['id' => $id]),

            'breadcrumb_data' => ['id' => $pay_order->purchase_id, 'sn' => $purchase_data->purchase_sn, 'type' => request('isFinalPay')],
            'product_grade_name' => $product_grade_name,
            'logistics_grade_name' => $logistics_grade_name,
            'logistics_price' => $logistics->logistics_price,
            'purchase_item_data' => $purchase_item_data,
            'deposit_payment_data' => $deposit_payment_data,
            'pay_order' => $pay_order,
            'currency' => $currency,
            'type' => request('isFinalPay') === '0' ? 'deposit' : 'final',
            'purchase_data' => $purchase_data,
            'supplier' => $supplier,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountPayable  $accountPayable
     * @param  int  $id primary ID of AccountPayable
     * @return \Illuminate\Http\Response
     */
    public function update(AccountPayable $accountPayable, Request $request, int $id)
    {
        $request->validate([
            'acc_transact_type_fk'    => ['required', 'string', 'regex:/^[1-6]$/'],
//            'pay_order_type' => ['required', 'string', 'regex:/^(pcs)$/'],
//            'pay_order_id' => ['required', 'int', 'min:1'],
//            'is_final_payment' => ['required', 'int', 'regex:/^(0|1)$/']
        ]);
        $req = $request->all();
        $requestPayableTypeId = $req['acc_transact_type_fk'];

        $requestUpdatePayableType = PayableModelType::getDescription($requestPayableTypeId);
        $oriPayableType = $accountPayable->find($id)->payable_type;
        $hasTheSamePayableType = ($oriPayableType === $requestUpdatePayableType) ? true :false;

        // when user choose other types instead of the original one
        // Flow Chart
        // TODO refactor in the future
//        if (the same) {
//            switch
//              update child tree
//              update parent tree
//        }else {
//            delete child tree
//            switch
//                create new child tree
//                update parent tree(update child id)
//        }
        if ($hasTheSamePayableType) {
            $payableId = $accountPayable->find($id)->payable->id;
            switch ($requestPayableTypeId) {
                case Payment::Cash:
                    PayableCash::where('id', $payableId)->update([
                        'grade_type' => PayableDefault::getModelNameByPayableTypeId(Payment::Cash),
                        'grade_id' => $req['cash']['grade_id_fk']
                    ]);
                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::Cash,
                        'payable_type' => 'App\Models\PayableCash',
                        'all_grades_id' => $req['cash']['grade_id_fk'],
                        'tw_price' => $req['tw_price'],
                        //            'payable_status' => $req['payable_status'],
                        'payment_date' => $req['payment_date'],
                        'accountant_id_fk' => Auth::user()->id,
                        'note' => $req['note'],
                    ]);
                    break;
                case Payment::Cheque:
                    PayableCheque::where('id', $payableId)->update([
                        'check_num' => $req['cheque']['check_num'],
                        'grade_type' => PayableDefault::getModelNameByPayableTypeId(Payment::Cheque),
                        'grade_id' => $req['cheque']['grade_id_fk'],
                        'maturity_date' => $req['cheque']['maturity_date'],
                        'cash_cheque_date' => $req['cheque']['cash_cheque_date'],
                        'cheque_status' => $req['cheque']['cheque_status'],
                    ]);
                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::Cheque,
                        'payable_type' => 'App\Models\PayableCheque',
                        'all_grades_id' => $req['cheque']['grade_id_fk'],
                        'tw_price' => $req['tw_price'],
                        //            'payable_status' => $req['payable_status'],
                        'payment_date' => $req['payment_date'],
                        'accountant_id_fk' => Auth::user()->id,
                        'note' => $req['note'],
                    ]);
                    break;
                case Payment::Remittance:
                    PayableRemit::where('id', $payableId)->update([
                        'grade_type' => PayableDefault::getModelNameByPayableTypeId(Payment::Remittance),
                        'grade_id' => $req['remit']['grade_id_fk'],
                        'remit_date' => $req['remit']['remit_date']
                    ]);

                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::Remittance,
                        'payable_type' => 'App\Models\PayableRemit',
                        'all_grades_id' => $req['remit']['grade_id_fk'],
                        'tw_price' => $req['tw_price'],
                        //            'payable_status' => $req['payable_status'],
                        'payment_date' => $req['payment_date'],
                        'accountant_id_fk' => Auth::user()->id,
                        'note' => $req['note'],
                    ]);
                    break;
                case Payment::ForeignCurrency:
                    PayableForeignCurrency::where('id', $payableId)->update([
                        'foreign_currency' => $req['foreign_currency']['foreign_price'],
                        'rate' => $req['foreign_currency']['rate'],
                        'grade_type' => PayableDefault::getModelNameByPayableTypeId(Payment::ForeignCurrency),
                        'grade_id' => $req['foreign_currency']['grade_id_fk'],
                        'acc_currency_fk' => $req['foreign_currency']['currency'],
                    ]);
                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::ForeignCurrency,
                        'payable_type' => 'App\Models\PayableForeignCurrency',
                        'all_grades_id' => $req['foreign_currency']['grade_id_fk'],
                        'tw_price' => $req['tw_price'],
                        //            'payable_status' => $req['payable_status'],
                        'payment_date' => $req['payment_date'],
                        'accountant_id_fk' => Auth::user()->id,
                        'note' => $req['note'],
                    ]);
                    break;
                case Payment::AccountsPayable:
                    PayableAccount::where('id', $payableId)->update([
                        'grade_type' => PayableDefault::getModelNameByPayableTypeId(Payment::AccountsPayable),
                        'grade_id' => $req['payable_account']['grade_id_fk'],
                    ]);
                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::AccountsPayable,
                        'payable_type' => 'App\Models\PayableAccount',
                        'all_grades_id' => $req['payable_account']['grade_id_fk'],
                        'tw_price' => $req['tw_price'],
                        //            'payable_status' => $req['payable_status'],
                        'payment_date' => $req['payment_date'],
                        'accountant_id_fk' => Auth::user()->id,
                        'note' => $req['note'],
                    ]);
                    break;
                case Payment::Other:
                    PayableOther::where('id', $payableId)->update([
                        'grade_type' => PayableDefault::getModelNameByPayableTypeId(Payment::Other),
                        'grade_id' => $req['other']['grade_id_fk']
                    ]);
                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::Other,
                        'payable_type' => 'App\Models\PayableOther',
                        'all_grades_id' => $req['other']['grade_id_fk'],
                        'tw_price' => $req['tw_price'],
                        //            'payable_status' => $req['payable_status'],
                        'payment_date' => $req['payment_date'],
                        'accountant_id_fk' => Auth::user()->id,
                        'note' => $req['note'],
                    ]);
                    break;
            }
        } else {
            $accountPayable->find($id)->payable->delete();
            switch ($requestPayableTypeId) {
                case Payment::Cash:
                    $payableData = PayableCash::create([
                        'grade_type' => PayableDefault::getModelNameByPayableTypeId(Payment::Cash),
                        'grade_id' => $req['cash']['grade_id_fk']
                    ]);
                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::Cash,
                        'payable_type' => 'App\Models\PayableCash',
                        'payable_id' => $payableData->id,
                        'all_grades_id' => $req['cash']['grade_id_fk'],
                        'tw_price' => $req['tw_price'],
                        //            'payable_status' => $req['payable_status'],
                        'payment_date' => $req['payment_date'],
                        'accountant_id_fk' => Auth::user()->id,
                        'note' => $req['note'],
                    ]);
                    break;
                case Payment::Cheque:
                    $payableData = PayableCheque::create([
                        'check_num' => $req['cheque']['check_num'],
                        'grade_type' => PayableDefault::getModelNameByPayableTypeId(Payment::Cheque),
                        'grade_id' => $req['cheque']['grade_id_fk'],
                        'maturity_date' => $req['cheque']['maturity_date'],
                        'cash_cheque_date' => $req['cheque']['cash_cheque_date'],
                        'cheque_status' => $req['cheque']['cheque_status'],
                    ]);
                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::Cheque,
                        'payable_type' => 'App\Models\PayableCheque',
                        'payable_id' => $payableData->id,
                        'all_grades_id' => $req['cheque']['grade_id_fk'],
                        'tw_price' => $req['tw_price'],
                        //            'payable_status' => $req['payable_status'],
                        'payment_date' => $req['payment_date'],
                        'accountant_id_fk' => Auth::user()->id,
                        'note' => $req['note'],
                    ]);
                    break;
                case Payment::Remittance:
                    $payableData = PayableRemit::create([
                        'grade_type' => PayableDefault::getModelNameByPayableTypeId(Payment::Remittance),
                        'grade_id' => $req['remit']['grade_id_fk'],
                        'remit_date' => $req['remit']['remit_date']
                    ]);
                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::Remittance,
                        'payable_type' => 'App\Models\PayableRemit',
                        'payable_id' => $payableData->id,
                        'all_grades_id' => $req['remit']['grade_id_fk'],
                        'tw_price' => $req['tw_price'],
                        //            'payable_status' => $req['payable_status'],
                        'payment_date' => $req['payment_date'],
                        'accountant_id_fk' => Auth::user()->id,
                        'note' => $req['note'],
                    ]);
                    break;
                case Payment::ForeignCurrency:
                    $payableData = PayableForeignCurrency::create([
                        'foreign_currency' => $req['foreign_currency']['foreign_price'],
                        'rate' => $req['foreign_currency']['rate'],
                        'grade_type' => PayableDefault::getModelNameByPayableTypeId(Payment::ForeignCurrency),
                        'grade_id' => $req['foreign_currency']['grade_id_fk'],
                        'acc_currency_fk' => $req['foreign_currency']['currency'],
                    ]);
                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::ForeignCurrency,
                        'payable_type' => 'App\Models\PayableForeignCurrency',
                        'payable_id' => $payableData->id,
                        'all_grades_id' => $req['foreign_currency']['grade_id_fk'],
                        'tw_price' => $req['tw_price'],
                        //            'payable_status' => $req['payable_status'],
                        'payment_date' => $req['payment_date'],
                        'accountant_id_fk' => Auth::user()->id,
                        'note' => $req['note'],
                    ]);
                    break;
                case Payment::AccountsPayable:
                    $payableData = AccountPayable::create([
                        'grade_type' => PayableDefault::getModelNameByPayableTypeId(Payment::AccountsPayable),
                        'grade_id' => $req['payable_account']['grade_id_fk'],
                    ]);
                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::AccountsPayable,
                        'payable_type' => 'App\Models\PayableAccount',
                        'payable_id' => $payableData->id,
                        'all_grades_id' => $req['payable_account']['grade_id_fk'],
                        'tw_price' => $req['tw_price'],
                        //            'payable_status' => $req['payable_status'],
                        'payment_date' => $req['payment_date'],
                        'accountant_id_fk' => Auth::user()->id,
                        'note' => $req['note'],
                    ]);
                    break;
                case Payment::Other:
                    $payableData =PayableOther::create([
                        'grade_type' => PayableDefault::getModelNameByPayableTypeId(Payment::Other),
                        'grade_id' => $req['other']['grade_id_fk']
                    ]);
                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::Other,
                        'payable_type' => 'App\Models\PayableOther',
                        'payable_id' => $payableData->id,
                        'all_grades_id' => $req['other']['grade_id_fk'],
                        'tw_price' => $req['tw_price'],
                        //            'payable_status' => $req['payable_status'],
                        'payment_date' => $req['payment_date'],
                        'accountant_id_fk' => Auth::user()->id,
                        'note' => $req['note'],
                    ]);
                    break;
            }
        }

        return redirect()->route('cms.purchase.view-pay-order',
                                                    ['id' => $req['purchase_id'],
                                                     'type' => $req['is_final_payment']]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AccountPayable  $accountPayable
     * @return \Illuminate\Http\Response
     */
    public function destroy(AccountPayable $accountPayable)
    {
        //
    }

}
