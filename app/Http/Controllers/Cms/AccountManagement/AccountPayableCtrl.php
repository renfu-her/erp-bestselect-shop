<?php

namespace App\Http\Controllers\Cms\AccountManagement;

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
                                        ['id' => $req['pay_order_id'],
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
     * @return \Illuminate\Http\Response
     */
    public function edit(AccountPayable $accountPayable)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountPayable  $accountPayable
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AccountPayable $accountPayable)
    {
        //
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
