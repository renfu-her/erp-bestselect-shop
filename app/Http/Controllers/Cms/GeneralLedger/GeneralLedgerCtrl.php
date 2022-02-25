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
        $totalGrades = GeneralLedger::getGradeData(0);
//        $query = $request->query();
//        $data_per_page = Arr::get($query, 'data_per_page', 10);
//        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;
//
//        $currentFirstGradeId = Arr::get($query, 'firstGrade', 1);
//        $currentFirstGradeId = is_numeric($currentFirstGradeId) ? $currentFirstGradeId : 1;

        return view('cms.accounting.general_ledger.list', [
            'totalGrades' => $totalGrades,
//            'currentFirstGradeId' => $currentFirstGradeId,
//            'data_per_page' => $data_per_page,
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
            'currentCode' => $request['code'],
//            'data' => GeneralLedger::getDataByGrade($id, $currentGrade[1])[0],
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
        $currentUri = Route::getCurrentRoute()->uri;
        preg_match('/cms\\/general_ledger\\/create\\/(1st|2nd|3rd|4th)$/', $currentUri, $currentGrade);

        $request->validate([
            'name' => 'required|string',
            'code' => 'required|string',
            'has_next_grade' => 'required|string',
            'acc_company_fk' => 'nullable|string',
            'acc_income_statement_fk' => 'nullable|string',
            'note_1' => 'nullable|string',
            'note_2' => 'nullable|string',
        ]);

        $req = $request->only(
            'name',
            'code',
            'has_next_grade',
            'acc_company_fk',
            'acc_income_statement_fk',
            'note_1',
            'note_2',
        );

        GeneralLedger::storeGradeData($req, $currentGrade[1][0]);

        return redirect(Route('cms.general_ledger.index'));
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
            'dataList' => GeneralLedger::getDataByGrade($id, $currentGrade[1][0]),
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
            'data' => GeneralLedger::getDataByGrade($id, $currentGrade[1][0])[0],
            'isFourthGradeExist' => ($currentGrade[1] == '4th') ? true : false,
            'allCompanies' => DB::table('acc_company')->get(),
            'allCategories' => DB::table('acc_income_statement')->get(),
            'currentGrade' => $currentGrade[1],
            'formAction' => Route('cms.general_ledger.update-' . $currentGrade[1], ['id' => $id])
            //            'data_per_page' => $data_per_page,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        $currentUri = Route::getCurrentRoute()->uri;
        preg_match('/cms\\/general_ledger\\/edit\\/{id}\\/(1st|2nd|3rd|4th)$/', $currentUri, $currentGrade);

        $request->validate([
            'name' => 'required|string',
            'has_next_grade' => 'required|string',
            'acc_company_fk' => 'nullable|string',
            'acc_income_statement_fk' => 'nullable|string',
            'note_1' => 'nullable|sting',
            'note_2' => 'nullable|sting',
        ]);

        $req = $request->only(
            'name',
            'has_next_grade',
            'acc_company_fk',
            'acc_income_statement_fk',
            'note_1',
            'note_2',
        );

        $tableNameArray = [
            '1st' => 'acc_first_grade',
            '2nd' => 'acc_second_grade',
            '3rd' => 'acc_third_grade',
            '4th' => 'acc_fourth_grade',
        ];

        DB::table($tableNameArray[$currentGrade[1]])
            ->where('id', '=', $id)
            ->update($req);
        return redirect(Route('cms.general_ledger.index'));
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
