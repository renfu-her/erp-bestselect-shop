<?php

namespace App\Http\Controllers\Cms\Accounting;

use App\Http\Controllers\Controller;
use App\Models\FirstGrade;
use App\Models\GeneralLedger;
use App\Models\SecondGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class GeneralLedgerCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $request->query();
        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;

        $currentFirstGradeId = Arr::get($query, 'firstGrade', 1);
        $currentFirstGradeId = is_numeric($currentFirstGradeId) ? $currentFirstGradeId : 1;

        $secondGrades = GeneralLedger::getSecondGradeById($currentFirstGradeId);

        $thirdGrades = array();
        foreach ($secondGrades as $secondGrade) {
            $thirdGrades[] = GeneralLedger::getThirdGradeById($secondGrade->id);
        }

        $fourthGrades = array();
        foreach ($thirdGrades as $thirdGrade) {
            foreach ($thirdGrade as $thirdGradeId) {
                $fourthGrades[] = GeneralLedger::getFourthGradeById($thirdGradeId->id);
            }
        }

        return view('cms.accounting.general_ledger.list', [
            'firstGrades' => FirstGrade::all(),
            'secondGrades' => $secondGrades,
            'thirdGrades' => $thirdGrades,
            'fourthGrades' => $fourthGrades,
            'currentFirstGradeId' => $currentFirstGradeId,
            'data_per_page' => $data_per_page,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (isset($request['currentGrade'])){
            $grade = $request['currentGrade'];
        }
        if (isset($request['nextGrade'])){
            $grade = $request['nextGrade'];
        }

        return view('cms.accounting.general_ledger.edit', [
            'method' => 'create',
            'allCompanies' => DB::table('acc_company')->get(),
            'allCategories' => DB::table('acc_income_statement')->get(),
            'isFourthGradeExist' => ($grade === '4th') ? true : false,
            'formAction' => Route('cms.general_ledger.store-' . $grade),
        ]);
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
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, int $id)
    {
        $currentUri = Route::getCurrentRoute()->uri;
        preg_match('/cms\\/general_ledger\\/show\\/{id}\\/(1st|2nd|3rd|4th)$/', $currentUri, $currentGrade);

        $isFourthGradeExist = ($currentGrade[1] == '4th') ? true : false;

        $nextGrade = '';
        if (!$isFourthGradeExist) {
            $gradeNameArray = [
                '1st',
                '2nd',
                '3rd',
                '4th',
            ];
            $key = array_search($currentGrade[1], $gradeNameArray);
            for ($i = 0; $i <= $key; $i++) {
                $nextGrade = next($gradeNameArray);
            }
        }

        return view('cms.accounting.general_ledger.show', [
            'method' => 'show',
            'dataList' => GeneralLedger::getDataByGrade($id, $currentGrade[1]),
            'isFourthGradeExist' => $isFourthGradeExist,
            'currentGrade' => $currentGrade[1],
            'nextGrade' => $nextGrade,
            'formAction' => ''
            //            'data_per_page' => $data_per_page,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, int $id)
    {
        $currentUri = Route::getCurrentRoute()->uri;
        preg_match('/cms\\/general_ledger\\/edit\\/{id}\\/(1st|2nd|3rd|4th)$/', $currentUri, $currentGrade);

        return view('cms.accounting.general_ledger.edit', [
            'method' => 'edit',
            'data' => GeneralLedger::getDataByGrade($id, $currentGrade[1])[0],
            'isFourthGradeExist' => ($currentGrade[1] == '4th') ? true : false,
            'allCompanies' => DB::table('acc_company')->get(),
            'allCategories' => DB::table('acc_income_statement')->get(),
            'currentGrade' => $currentGrade[1],
            'formAction' => ''
            //            'data_per_page' => $data_per_page,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GeneralLedger  $generalLedger
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GeneralLedger $generalLedger)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GeneralLedger  $generalLedger
     * @return \Illuminate\Http\Response
     */
    public function destroy(GeneralLedger $generalLedger)
    {
        //
    }
}
