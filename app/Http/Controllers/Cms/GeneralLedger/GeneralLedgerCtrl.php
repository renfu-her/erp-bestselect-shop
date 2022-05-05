<?php

namespace App\Http\Controllers\Cms\GeneralLedger;

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
        $firstGrades = GeneralLedger::getAllFirstGrade();
        $totalGrades = [];

        foreach ($firstGrades as $firstGrade) {
            foreach (GeneralLedger::getSecondGradeById($firstGrade['id']) as $secondGrade) {
                foreach (GeneralLedger::getThirdGradeById($secondGrade['id']) as $thirdGrade) {
                    $thirdGrade['fourth'] = GeneralLedger::getFourthGradeById($thirdGrade['id']);
                    $secondGrade['third'][] = $thirdGrade;
                }
                $firstGrade['second'][] = $secondGrade;
            }
            $totalGrades[] = $firstGrade;
        }

        return view('cms.general_ledger.gl.list', [
            'totalGrades' => $totalGrades,
        ]);
    }


    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, int $id, $type)
    {
        if(! array_key_exists($type[0], GeneralLedger::GRADE_TABALE_NAME_ARRAY)){
            return abort(404);
        }

        $table = GeneralLedger::GRADE_TABALE_NAME_ARRAY[$type[0]];

        $request->merge([
            'id'=>$id,
            'type'=>$type,
        ]);

        $request->validate([
            'id' => 'required|exists:' . $table . ',id',
            'type' => 'required|in:1st,2nd,3rd,4th',
        ]);

        $isFourthGradeExist = ($type == '4th') ? true : false;

        $nextGrade = '';
        if (!$isFourthGradeExist) {
            $gradeNameArray = [
                '1st',
                '2nd',
                '3rd',
                '4th',
            ];
            $key = array_search($type, $gradeNameArray);
            for ($i = 0; $i <= $key; $i++) {
                $nextGrade = next($gradeNameArray);
            }
        }

        return view('cms.general_ledger.gl.show', [
            'method' => 'show',
            'dataList' => GeneralLedger::getDataByGrade($id, $table),
            'isFourthGradeExist' => $isFourthGradeExist,
            'currentGrade' => $type,
            'nextGrade' => $nextGrade,
            'formAction' => ''
        ]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->merge([
            'currentGrade'=>request('currentGrade'),
            'nextGrade'=>request('nextGrade'),
            'code'=>request('code'),
        ]);

        $request->validate([
            'currentGrade' => 'nullable|in:1st,2nd,3rd,4th',
            'nextGrade' => 'nullable|in:1st,2nd,3rd,4th',
            'code' => 'required',
        ]);

        $grade = '1st';

        if(request('nextGrade')){
            $grade = request('nextGrade');
        }

        if(request('currentGrade')){
            $grade = request('currentGrade');
        }

        return view('cms.general_ledger.gl.edit', [
            'method' => 'create',
            'currentCode' => $request['code'],
            'allCompanies' => DB::table('acc_company')->get(),
            'allCategories' => DB::table('acc_income_statement')->get(),
            'isFourthGradeExist' => ($grade === '4th') ? true : false,
            'formAction' => Route('cms.general_ledger.store', ['type'=>$grade]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $type)
    {
        $request->merge([
            'type'=>$type,
        ]);

        $request->validate([
            'type' => 'required|in:1st,2nd,3rd,4th',
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

        GeneralLedger::storeGradeData($req, $type[0]);

        return redirect(Route('cms.general_ledger.index'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, int $id, $type)
    {
        if(! array_key_exists($type[0], GeneralLedger::GRADE_TABALE_NAME_ARRAY)){
            return abort(404);
        }

        $table = GeneralLedger::GRADE_TABALE_NAME_ARRAY[$type[0]];

        $request->merge([
            'id'=>$id,
            'type'=>$type,
        ]);

        $request->validate([
            'id' => 'required|exists:' . $table . ',id',
            'type' => 'required|in:1st,2nd,3rd,4th',
        ]);

        return view('cms.general_ledger.gl.edit', [
            'method' => 'edit',
            'data' => GeneralLedger::getDataByGrade($id, $table)->first(),
            'isFourthGradeExist' => ($type == '4th') ? true : false,
            'allCompanies' => DB::table('acc_company')->get(),
            'allCategories' => DB::table('acc_income_statement')->get(),
            'currentGrade' => $type,
            'formAction' => Route('cms.general_ledger.update', ['id' => $id, 'type' => $type])
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id, $type)
    {
        if(! array_key_exists($type[0], GeneralLedger::GRADE_TABALE_NAME_ARRAY)){
            return abort(404);
        }

        $table = GeneralLedger::GRADE_TABALE_NAME_ARRAY[$type[0]];

        $request->merge([
            'id'=>$id,
            'type'=>$type,
        ]);

        $request->validate([
            'id' => 'required|exists:' . $table . ',id',
            'type' => 'required|in:1st,2nd,3rd,4th',
        ]);

        $request->validate([
            'name' => 'required|string',
            'has_next_grade' => 'required|string',
            'acc_company_fk' => 'nullable|string',
            'acc_income_statement_fk' => 'nullable|string',
            'note_1' => 'nullable|string',
            'note_2' => 'nullable|string',
        ]);

        $req = $request->only(
            'name',
            'has_next_grade',
            'acc_company_fk',
            'acc_income_statement_fk',
            'note_1',
            'note_2',
        );

        DB::table($table)->where('id', '=', $id)->update($req);

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
