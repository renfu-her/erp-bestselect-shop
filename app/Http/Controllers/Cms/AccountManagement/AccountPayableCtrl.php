<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Enums\Payable\PayableModelType;
use App\Enums\Supplier\Payment;
use App\Http\Controllers\Controller;
use App\Models\AccountPayable;
use App\Models\PayableAccount;
use App\Models\PayableCash;
use App\Models\IncomeExpenditure;
use App\Models\PayableCheque;
use App\Models\PayableForeignCurrency;
use App\Models\PayableOther;
use App\Models\PayableRemit;
use App\Models\PayingOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\Payable\PayableStatus;

class AccountPayableCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->validate([
            'payOrdType'    => ['required', 'string', 'regex:/^(pcs)$/'],
            'payOrdId' => ['required', 'int', 'min:1']
        ]);

        $payOrdId = $request['payOrdId'];

        if ($request['type'] === 'pcs') {
            $type = 'App\Models\PayingOrder';
        }

        $payOrder = PayingOrder::find($payOrdId);
        $thirdGradesDataList = IncomeExpenditure::getOptionDataByGrade(3);
        $fourthGradesDataList = IncomeExpenditure::getOptionDataByGrade(4);
        $currencyData = IncomeExpenditure::getCurrencyOptionData();

        $payStatusArray = [
            [
                'id' => PayableStatus::Unpaid,
                'payment_status' => PayableStatus::getDescription(PayableStatus::Unpaid)
            ],
            [
                'id' => PayableStatus::Paid,
                'payment_status' => PayableStatus::getDescription(PayableStatus::Paid)
            ]
        ];

        return view('cms.account_management.account_payable.edit', [
            'tw_price' => $payOrder->price,
            'thirdGradesDataList' => $thirdGradesDataList,
            'fourthGradesDataList' => $fourthGradesDataList,
            'cashDefault' => AccountPayable::getThirdGradeDefaultById(1),
            'chequeDefault' => AccountPayable::getFourthGradeDefaultById(2),
            'remitDefault' => AccountPayable::getFourthGradeDefaultById(3),
            'currencyDefault' => AccountPayable::getFourthGradeDefaultById(4),
            'accountPayableDefault' => AccountPayable::getFourthGradeDefaultById(5),
            'otherDefault' => AccountPayable::getThirdGradeDefaultById(6),
            'paymentStatusList' => $payStatusArray,
            'currencyData' => $currencyData,
            'method' => 'create',
            'transactTypeList' => AccountPayable::getTransactTypeList(),
            'chequeStatus' => AccountPayable::getChequeStatus(),
            'formAction' => Route('cms.ap.store'),
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
            'pay_order_id' => ['required', 'int', 'min:1'],
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

        return redirect()->route('cms.purchase.view-pay-order',
                                        ['id' => $req['purchase_id'],
                                        'type' => $req['is_final_payment']]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AccountPayable  $accountPayable
     * @return \Illuminate\Http\Response
     */
    public function show(AccountPayable $accountPayable)
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
    public function edit(AccountPayable $accountPayable, Request $request, int $id)
    {
        $request->validate([
            'payOrdType'    => ['required', 'string', 'regex:/^(pcs)$/'],
            'payOrdId' => ['required', 'int', 'min:1']
        ]);

        $payOrdId = $request['payOrdId'];

        $payableData = $accountPayable::find($id);
        $payableType = $payableData->acc_income_type_fk;

        $payableTypeData = $payableData->payable;
        $grade = $payableTypeData->grade;

        switch ($payableType) {
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
        $allPayableTypeData = [
          'payableCash' => $payableCash ?? [],
          'payableCheque' => $payableCheque ?? [],
          'payableRemit' => $payableRemit ?? [],
          'payableForeignCurrency' => $payableForeignCurrency ?? [],
          'payableAccount' => $payableAccount ?? [],
          'payableOther' => $payableOther ?? [],
        ];

        $payOrder = PayingOrder::find($payOrdId);
        $thirdGradesDataList = IncomeExpenditure::getOptionDataByGrade(3);
        $fourthGradesDataList = IncomeExpenditure::getOptionDataByGrade(4);
        $currencyData = IncomeExpenditure::getCurrencyOptionData();

        $payStatusArray = [
            [
                'id' => PayableStatus::Unpaid,
                'payment_status' => PayableStatus::getDescription(PayableStatus::Unpaid)
            ],
            [
                'id' => PayableStatus::Paid,
                'payment_status' => PayableStatus::getDescription(PayableStatus::Paid)
            ]
        ];

        return view('cms.account_management.account_payable.edit', [
            'tw_price' => $payableData->tw_price,
            'payableData' => $payableData,
            'allPayableTypeData' => $allPayableTypeData,
            'payment_date' => explode(' ', $payableData->payment_date)[0],
//            'note' => $payableData->note ?? '',
            'thirdGradesDataList' => $thirdGradesDataList,
            'fourthGradesDataList' => $fourthGradesDataList,
            'cashDefault' => AccountPayable::getThirdGradeDefaultById(1),
            'chequeDefault' => AccountPayable::getFourthGradeDefaultById(2),
            'remitDefault' => AccountPayable::getFourthGradeDefaultById(3),
            'currencyDefault' => AccountPayable::getFourthGradeDefaultById(4),
            'accountPayableDefault' => AccountPayable::getFourthGradeDefaultById(5),
            'otherDefault' => AccountPayable::getThirdGradeDefaultById(6),
            'paymentStatusList' => $payStatusArray,
            'currencyData' => $currencyData,
            'method' => 'edit',
            'transactTypeList' => AccountPayable::getTransactTypeList(),
            'chequeStatus' => AccountPayable::getChequeStatus(),
            'formAction' => Route('cms.ap.update', ['id' => $id]),
        ]);
        //
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
