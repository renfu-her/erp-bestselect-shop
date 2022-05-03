<?php

namespace App\Http\Controllers\Cms\Accounting;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\GeneralLedger;
use App\Models\IncomeExpenditure;
use App\Models\PayableDefault;

class IncomeExpenditureCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currencyData = PayableDefault::getCurrencyOptionData();

        $productGradeDefaultArray = PayableDefault::where('name', 'product')->first();
        $logisticsGradeDefaultArray = PayableDefault::where('name', 'logistics')->first();

        $firstGrades = GeneralLedger::getAllFirstGrade();
        $totalGrades = array();
        foreach ($firstGrades as $firstGrade) {
            $totalGrades[] = $firstGrade;
            foreach (GeneralLedger::getSecondGradeById($firstGrade['id']) as $secondGrade) {
                $totalGrades[] = $secondGrade;
                foreach (GeneralLedger::getThirdGradeById($secondGrade['id']) as $thirdGrade) {
                    $totalGrades[] = $thirdGrade;
                    foreach (GeneralLedger::getFourthGradeById($thirdGrade['id']) as $fourthGrade) {
                        $totalGrades[] = $fourthGrade;
                    }
                }
            }
        }

        $cash_data = PayableDefault::where('name', 'cash')->pluck('default_grade_id')->toArray();
        $cheque_data = PayableDefault::where('name', 'cheque')->pluck('default_grade_id')->toArray();
        $remittance_data = PayableDefault::where('name', 'remittance')->pluck('default_grade_id')->toArray();
        $accounts_payable_data = PayableDefault::where('name', 'accounts_payable')->pluck('default_grade_id')->toArray();
        $other_data = PayableDefault::where('name', 'other')->pluck('default_grade_id')->toArray();

        return view('cms.accounting.income_expenditure.edit', [
            'totalGrades' => $totalGrades,

            'currencyData' => $currencyData,

            'cash_data' => $cash_data,
            'cheque_data' => $cheque_data,
            'remittance_data' => $remittance_data,
            'accounts_payable_data' => $accounts_payable_data,
            'other_data' => $other_data,

            'productGradeDefaultArray' => $productGradeDefaultArray,
            'logisticsGradeDefaultArray' => $logisticsGradeDefaultArray,

            'isViewMode' => true,
            'formAction' => Route('cms.income_expenditure.edit', [], true),
            'formMethod' => 'GET'
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $currencyData = PayableDefault::getCurrencyOptionData();

        $productGradeDefaultArray = PayableDefault::where('name', 'product')->first();
        $logisticsGradeDefaultArray = PayableDefault::where('name', 'logistics')->first();

        $firstGrades = GeneralLedger::getAllFirstGrade();
        $totalGrades = array();
        foreach ($firstGrades as $firstGrade) {
            $totalGrades[] = $firstGrade;
            foreach (GeneralLedger::getSecondGradeById($firstGrade['id']) as $secondGrade) {
                $totalGrades[] = $secondGrade;
                foreach (GeneralLedger::getThirdGradeById($secondGrade['id']) as $thirdGrade) {
                    $totalGrades[] = $thirdGrade;
                    foreach (GeneralLedger::getFourthGradeById($thirdGrade['id']) as $fourthGrade) {
                        $totalGrades[] = $fourthGrade;
                    }
                }
            }
        }

        $cash_data = PayableDefault::where('name', 'cash')->pluck('default_grade_id')->toArray();
        $cheque_data = PayableDefault::where('name', 'cheque')->pluck('default_grade_id')->toArray();
        $remittance_data = PayableDefault::where('name', 'remittance')->pluck('default_grade_id')->toArray();
        $accounts_payable_data = PayableDefault::where('name', 'accounts_payable')->pluck('default_grade_id')->toArray();
        $other_data = PayableDefault::where('name', 'other')->pluck('default_grade_id')->toArray();

        return view('cms.accounting.income_expenditure.edit', [
            'totalGrades' => $totalGrades,

            'currencyData' => $currencyData,
            'cash_data' => $cash_data,
            'cheque_data' => $cheque_data,
            'remittance_data' => $remittance_data,
            'accounts_payable_data' => $accounts_payable_data,
            'other_data' => $other_data,

            'productGradeDefaultArray' => $productGradeDefaultArray,
            'logisticsGradeDefaultArray' => $logisticsGradeDefaultArray,

            'isViewMode' => false,
            'formAction' => Route('cms.income_expenditure.update', [], true),
            'formMethod' => 'POST'
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\IncomeExpenditure  $incomeExpenditure
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $allCurrencyIds = DB::table('acc_currency')
                            ->select('id')
                            ->orderBy('id')
                            ->get();

        $allCurrencyIdArray = [];
        foreach ($allCurrencyIds as $allCurrencyId) {
            $allCurrencyIdArray[] = $allCurrencyId->id;
        }

        $val = Validator::make($request->all(), [
            'income_type' => 'required|array:cash,cheque,remittance,accounts_payable,other',
            'income_type.*' => ['required', 'array'],
            'income_type.*.*' => ['nullable', 'int', 'min:1'],
            'currency' => ['required', 'array:' . implode(',', $allCurrencyIdArray)],
            'currency.*' => ['required', 'array:rate,gradeOption'],
            'currency.*.rate' => ['required', 'numeric', 'min:0'],
            'currency.*.gradeOption' => ['nullable', 'int', 'min:1'],
        ]);

        $validatedReq = $val->validated();

        PayableDefault::updateCurrency($validatedReq);
        PayableDefault::updateIncomeExpenditure($validatedReq);

        PayableDefault::where('name', 'product')->first()->update([
            'default_grade_id'=>$request['orderDefault']['product'],
        ]);

        PayableDefault::where('name', 'logistics')->first()->update([
            'default_grade_id'=>$request['orderDefault']['logistics'],
        ]);

        return redirect()->route('cms.income_expenditure.index');
    }
}
