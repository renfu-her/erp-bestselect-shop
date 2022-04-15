<?php

namespace App\Http\Controllers\Cms\AccountManagement;



use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Enums\Payable\PayableModelType;
use App\Enums\Supplier\Payment;
use App\Enums\Payable\PayableStatus;

use App\Models\AllGrade;
use App\Models\AccountPayable;
use App\Models\IncomeExpenditure;
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

class AccountPayableCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(AccountPayable $accountPayable)
    {
        $payableDataList = $accountPayable->all();

        $dataList = [];
        foreach ($payableDataList as $payableData) {
            $payingOrderRepresentative = DB::table('usr_users')
                                                ->find($payableData->payingOrder->usr_users_id, 'name')
                                                ->name;
            $accountant = DB::table('usr_users')
                            ->find($payableData->accountant_id_fk, 'name')
                            ->name;
            $dataList[] = [
                'tw_price' => $payableData->tw_price,
                //TODO use Enum Type to define PayingOrder Model
                'payingOrderType' => '採購',
                'paying_order_sn' => $payableData->payingOrder->sn,
                'paying_order_representative' => $payingOrderRepresentative,
                'payableTypeName' => AccountPayable::getPayableNameByModelName($payableData->payable_type),
                'accountant' => $accountant,
            ];
        }

        return view('cms.account_management.account_payable.list', [
            'dataList' => $dataList,
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
        // $thirdGradesDataList = IncomeExpenditure::getOptionDataByGrade(3);
        // $fourthGradesDataList = IncomeExpenditure::getOptionDataByGrade(4);
        // $currencyData = IncomeExpenditure::getCurrencyOptionData();

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

        $paid_paying_order_data = PayingOrder::where(function ($q) use($request){
                $q->where([
                    'purchase_id'=>$request['purchaseId'],
                    'deleted_at'=>null,
                ]);

                if($request['isFinalPay'] === '0'){
                    $q->where([
                        'type'=>0,
                    ]);
                }

            })->get();

        $payable_data = AccountPayable::whereIn('pay_order_id', $paid_paying_order_data->pluck('id')->toArray())->get();
        $tw_price = $paid_paying_order_data->sum('price') - $payable_data->sum('tw_price');
        if(request('isFinalPay') === '1') $tw_price += $logistics->logistics_price;

        return view('cms.account_management.account_payable.edit', [
            'tw_price' => $tw_price,
            'payable_data' => $payable_data,

            // 'thirdGradesDataList' => $thirdGradesDataList,
            // 'fourthGradesDataList' => $fourthGradesDataList,
            // 'currencyData' => $currencyData,
            // 'paymentStatusList' => $payStatusArray,
            'cashDefault' => AccountPayable::getThirdGradeDefaultById(1),
            'chequeDefault' => AccountPayable::getFourthGradeDefaultById(2),
            'remitDefault' => AccountPayable::getFourthGradeDefaultById(3),
            'currencyDefault' => AccountPayable::getFourthGradeDefaultById(4),
            'accountPayableDefault' => AccountPayable::getFourthGradeDefaultById(5),
            'otherDefault' => AccountPayable::getThirdGradeDefaultById(6),
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
        if ($pay_order->price == AccountPayable::where('pay_order_id', request('pay_order_id'))->sum('tw_price')) {
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
        // $thirdGradesDataList = IncomeExpenditure::getOptionDataByGrade(3);
        // $fourthGradesDataList = IncomeExpenditure::getOptionDataByGrade(4);
        // $currencyData = IncomeExpenditure::getCurrencyOptionData();

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
            'cashDefault' => AccountPayable::getThirdGradeDefaultById(1),
            'chequeDefault' => AccountPayable::getFourthGradeDefaultById(2),
            'remitDefault' => AccountPayable::getFourthGradeDefaultById(3),
            'currencyDefault' => AccountPayable::getFourthGradeDefaultById(4),
            'accountPayableDefault' => AccountPayable::getFourthGradeDefaultById(5),
            'otherDefault' => AccountPayable::getThirdGradeDefaultById(6),
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
                    PayableModelType::where('id', $payableId)->update([
                        'grade_type' => IncomeExpenditure::getModelNameByPayableTypeId(Payment::Cash),
                        'grade_id' => $req['cash']['grade_id_fk']
                    ]);
                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::Cash,
                        'payable_type' => 'App\Models\PayableCash',
//                        'payable_id' => $payableData->id,
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
                        'grade_type' => IncomeExpenditure::getModelNameByPayableTypeId(Payment::Cheque),
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
                        //                        'payable_id' => $payableData->id,
                        'tw_price' => $req['tw_price'],
                        //            'payable_status' => $req['payable_status'],
                        'payment_date' => $req['payment_date'],
                        'accountant_id_fk' => Auth::user()->id,
                        'note' => $req['note'],
                    ]);
                    break;
                case Payment::Remittance:
                    PayableRemit::where('id', $payableId)->update([
                        'grade_type' => IncomeExpenditure::getModelNameByPayableTypeId(Payment::Remittance),
                        'grade_id' => $req['remit']['grade_id_fk'],
                        'remit_date' => $req['remit']['remit_date']
                    ]);

                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::Remittance,
                        'payable_type' => 'App\Models\PayableRemit',
//                        'payable_id' => $payableData->id,
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
                        'grade_type' => IncomeExpenditure::getModelNameByPayableTypeId(Payment::ForeignCurrency),
                        'grade_id' => $req['foreign_currency']['grade_id_fk'],
                        'acc_currency_fk' => $req['foreign_currency']['currency'],
                    ]);
                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::ForeignCurrency,
                        'payable_type' => 'App\Models\PayableForeignCurrency',
//                        'payable_id' => $payableData->id,
                        'tw_price' => $req['tw_price'],
                        //            'payable_status' => $req['payable_status'],
                        'payment_date' => $req['payment_date'],
                        'accountant_id_fk' => Auth::user()->id,
                        'note' => $req['note'],
                    ]);
                    break;
                case Payment::AccountsPayable:
                    PayableAccount::where('id', $payableId)->update([
                        'grade_type' => IncomeExpenditure::getModelNameByPayableTypeId(Payment::AccountsPayable),
                        'grade_id' => $req['payable_account']['grade_id_fk'],
                    ]);
                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::AccountsPayable,
                        'payable_type' => 'App\Models\PayableAccount',
//                        'payable_id' => $payableData->id,
                        'tw_price' => $req['tw_price'],
                        //            'payable_status' => $req['payable_status'],
                        'payment_date' => $req['payment_date'],
                        'accountant_id_fk' => Auth::user()->id,
                        'note' => $req['note'],
                    ]);
                    break;
                case Payment::Other:
                    PayableOther::where('id', $payableId)->update([
                        'grade_type' => IncomeExpenditure::getModelNameByPayableTypeId(Payment::Other),
                        'grade_id' => $req['other']['grade_id_fk']
                    ]);
                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::Other,
                        'payable_type' => 'App\Models\PayableOther',
//                        'payable_id' => $payableData->id,
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
                        'grade_type' => IncomeExpenditure::getModelNameByPayableTypeId(Payment::Cash),
                        'grade_id' => $req['cash']['grade_id_fk']
                    ]);
                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::Cash,
                        'payable_type' => 'App\Models\PayableCash',
                        'payable_id' => $payableData->id,
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
                        'grade_type' => IncomeExpenditure::getModelNameByPayableTypeId(Payment::Cheque),
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
                        'tw_price' => $req['tw_price'],
                        //            'payable_status' => $req['payable_status'],
                        'payment_date' => $req['payment_date'],
                        'accountant_id_fk' => Auth::user()->id,
                        'note' => $req['note'],
                    ]);
                    break;
                case Payment::Remittance:
                    $payableData = PayableRemit::create([
                        'grade_type' => IncomeExpenditure::getModelNameByPayableTypeId(Payment::Remittance),
                        'grade_id' => $req['remit']['grade_id_fk'],
                        'remit_date' => $req['remit']['remit_date']
                    ]);
                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::Remittance,
                        'payable_type' => 'App\Models\PayableRemit',
                        'payable_id' => $payableData->id,
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
                        'grade_type' => IncomeExpenditure::getModelNameByPayableTypeId(Payment::ForeignCurrency),
                        'grade_id' => $req['foreign_currency']['grade_id_fk'],
                        'acc_currency_fk' => $req['foreign_currency']['currency'],
                    ]);
                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::ForeignCurrency,
                        'payable_type' => 'App\Models\PayableForeignCurrency',
                        'payable_id' => $payableData->id,
                        'tw_price' => $req['tw_price'],
                        //            'payable_status' => $req['payable_status'],
                        'payment_date' => $req['payment_date'],
                        'accountant_id_fk' => Auth::user()->id,
                        'note' => $req['note'],
                    ]);
                    break;
                case Payment::AccountsPayable:
                    $payableData = AccountPayable::create([
                        'grade_type' => IncomeExpenditure::getModelNameByPayableTypeId(Payment::AccountsPayable),
                        'grade_id' => $req['payable_account']['grade_id_fk'],
                    ]);
                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::AccountsPayable,
                        'payable_type' => 'App\Models\PayableAccount',
                        'payable_id' => $payableData->id,
                        'tw_price' => $req['tw_price'],
                        //            'payable_status' => $req['payable_status'],
                        'payment_date' => $req['payment_date'],
                        'accountant_id_fk' => Auth::user()->id,
                        'note' => $req['note'],
                    ]);
                    break;
                case Payment::Other:
                    $payableData =PayableOther::create([
                        'grade_type' => IncomeExpenditure::getModelNameByPayableTypeId(Payment::Other),
                        'grade_id' => $req['other']['grade_id_fk']
                    ]);
                    AccountPayable::where('id', $id)->update([
                        'pay_order_type' => 'App\Models\PayingOrder',
                        'pay_order_id' => $req['pay_order_id'],
                        'acc_income_type_fk' => Payment::Other,
                        'payable_type' => 'App\Models\PayableOther',
                        'payable_id' => $payableData->id,
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
