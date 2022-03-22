<?php

namespace App\Http\Controllers\Cms\Accounting;

use App\Enums\Received\ReceivedMethod;
use App\Http\Controllers\Controller;
use App\Models\GeneralLedger;
use App\Models\ReceivedDefault;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceivedDefaultCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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

        $allReceivedMethod = ReceivedMethod::asArray();
        $queries = DB::table('acc_received_default')
            ->whereIn('name', $allReceivedMethod)
            //「外幣」另外會分類處理
            ->whereNotIn('name', [ReceivedMethod::ForeignCurrency])
            ->get();

        $defaultArray = [];
        foreach ($queries as $query) {
            $defaultArray[$query->name]['description'] = ReceivedMethod::getDescription($query->name);
            $defaultArray[$query->name]['default_grade_id'][] = $query->default_grade_id;
        }
        foreach ($allReceivedMethod as $receivedMethod) {
            // 若使用者無設定「付款方式」，則補上[]數值，只在前端顯示「付款方式」文字，至於「外幣」另外分類處理
            if (!array_key_exists($receivedMethod, $defaultArray) &&
                $receivedMethod !== ReceivedMethod::ForeignCurrency) {
                $defaultArray[$receivedMethod]['description'] = ReceivedMethod::getDescription($receivedMethod);
                $defaultArray[$receivedMethod]['default_grade_id'] = [];
            }
        }

        $currencyDefault = DB::table('acc_currency')
            ->leftJoin('acc_received_default', 'acc_currency.received_default_fk', '=', 'acc_received_default.id')
            ->select(
                'acc_currency.name as currency_name',
                'acc_currency.id as currency_id',
                'acc_currency.rate',
                'default_grade_id',
                'acc_received_default.name as method_name'
            )
            ->orderBy('acc_currency.id')
            ->get();

        $currencyDefaultArray = [];
        foreach ($currencyDefault as $default) {
                $currencyDefaultArray[ReceivedMethod::ForeignCurrency][] = [
                    'currency_id'    => $default->currency_id,
                    'currency_name'    => $default->currency_name,
                    'rate'             => $default->rate,
                    'default_grade_id' => $default->default_grade_id ?? null,
                ];
        }

        return view('cms.accounting.received_default.edit', [
            'totalGrades' => $totalGrades,
            'defaultArray' => $defaultArray,
            'currencyDefaultArray' => $currencyDefaultArray,
            'isViewMode' => true,
            'formAction' => Route('cms.received_default.edit', [], true),
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
     * @param  \App\Models\ReceivedDefault  $receivedDefault
     * @return \Illuminate\Http\Response
     */
    public function show(ReceivedDefault $receivedDefault)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ReceivedDefault  $receivedDefault
     * @return \Illuminate\Http\Response
     */
    public function edit(ReceivedDefault $receivedDefault)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReceivedDefault  $receivedDefault
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ReceivedDefault $receivedDefault)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReceivedDefault  $receivedDefault
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReceivedDefault $receivedDefault)
    {
        //
    }
}
