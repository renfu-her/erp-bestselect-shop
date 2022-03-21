<?php

namespace App\Http\Controllers\Cms\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AllGrade;
use App\Models\FirstGrade;
use App\Models\GeneralLedger;
use App\Models\IncomeExpenditure;
use App\Models\GradeDefault;
use App\Enums\Accounting\ItemNameGradeDefault;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class IncomeExpenditureCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $productGradeDefaultArray = IncomeExpenditure::productGradeDefault();
        $logisticsGradeDefaultArray = IncomeExpenditure::logisticsGradeDefault();
        $allThirdGrades = GeneralLedger::getGradeData(3);
        $thirdGradesDataList = IncomeExpenditure::getOptionDataByGrade(3);
        $fourthGradesDataList = IncomeExpenditure::getOptionDataByGrade(4);
        $currencyData = IncomeExpenditure::getCurrencyOptionData();

        return view('cms.accounting.income_expenditure.edit', [
            'thirdGradesDataList' => $thirdGradesDataList,
            'fourthGradesDataList' => $fourthGradesDataList,
            'currencyData' => $currencyData,
            'productGradeDefaultArray' => $productGradeDefaultArray,
            'logisticsGradeDefaultArray' => $logisticsGradeDefaultArray,
            'allThirdGrades' => $allThirdGrades,
            'isViewMode' => true,
            'formAction' => Route('cms.income_expenditure.edit', [], true),
            'formMethod' => 'GET'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
     * @param  \App\Models\IncomeExpenditure  $incomeExpenditure
     * @return \Illuminate\Http\Response
     */
    public function show(IncomeExpenditure $incomeExpenditure)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $productGradeDefaultArray = IncomeExpenditure::productGradeDefault();
        $logisticsGradeDefaultArray = IncomeExpenditure::logisticsGradeDefault();
        $allThirdGrades = GeneralLedger::getGradeData(3);
        $thirdGradesDataList = IncomeExpenditure::getOptionDataByGrade(3);
        $fourthGradesDataList = IncomeExpenditure::getOptionDataByGrade(4);
        $currencyData = IncomeExpenditure::getCurrencyOptionData();

        return view('cms.accounting.income_expenditure.edit', [
            'thirdGradesDataList'  => $thirdGradesDataList,
            'fourthGradesDataList' => $fourthGradesDataList,
            'currencyData'         => $currencyData,
            'productGradeDefaultArray' => $productGradeDefaultArray,
            'logisticsGradeDefaultArray' => $logisticsGradeDefaultArray,
            'allThirdGrades' => $allThirdGrades,
            'isViewMode'           => false,
            'formAction'           => Route('cms.income_expenditure.update', [], true),
            'formMethod'           => 'POST'
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\IncomeExpenditure  $incomeExpenditure
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, IncomeExpenditure $incomeExpenditure)
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
            'income_type' => ['required', 'array'],
            'income_type.*' => ['required', 'array'],
            'income_type.*.*' => ['nullable', 'int', 'min:1'],
            'currency' => ['required', 'array:' . implode(',', $allCurrencyIdArray)],
            'currency.*' => ['required', 'array:rate,gradeOption'],
            'currency.*.rate' => ['required', 'numeric', 'min:0'],
            'currency.*.gradeOption' => ['nullable', 'int', 'min:1'],
        ]);

        $validatedReq = $val->validated();
        IncomeExpenditure::updateCurrency($validatedReq);
        IncomeExpenditure::updateIncomeExpenditure($validatedReq);

        GradeDefault::updateGradeDefault(ItemNameGradeDefault::Product, $request['orderDefault']['product']);
        GradeDefault::updateGradeDefault(ItemNameGradeDefault::Logistics, $request['orderDefault']['logistics']);

        return redirect()->route('cms.income_expenditure.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\IncomeExpenditure  $incomeExpenditure
     * @return \Illuminate\Http\Response
     */
    public function destroy(IncomeExpenditure $incomeExpenditure)
    {
        //
    }
}
