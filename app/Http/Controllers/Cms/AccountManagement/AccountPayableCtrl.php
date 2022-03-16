<?php

namespace App\Http\Controllers\Cms\AccountManagement;

use App\Http\Controllers\Controller;
use App\Models\AccountPayable;
use App\Models\PayableCash;
use App\Models\IncomeExpenditure;
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
            'type'    => ['required', 'string', 'regex:/^(pcs)$/'],
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
            'formAction' => Route('cms.ap.create'),
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
        //
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
