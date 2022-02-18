<?php

namespace App\Http\Controllers\Cms\Accounting;

use App\Http\Controllers\Controller;
use App\Models\FirstGrade;
use App\Models\GeneralLedger;
use App\Models\SecondGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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
     * @param  \App\Models\GeneralLedger  $generalLedger
     * @return \Illuminate\Http\Response
     */
    public function show(GeneralLedger $generalLedger)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GeneralLedger  $generalLedger
     * @return \Illuminate\Http\Response
     */
    public function edit(GeneralLedger $generalLedger)
    {
        //
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
