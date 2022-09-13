<?php

namespace App\Http\Controllers\Cms\Accounting;

use App\Http\Controllers\Controller;
use App\Models\FirstGrade;
use App\Models\GeneralLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class FirstGradeCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();
        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;

        $dataList =  FirstGrade::paginate($data_per_page)->appends($query);

        return view('cms.accounting.first_grade.list', [
            'dataList' => $dataList,
            'data_per_page' => $data_per_page,
        ]);
    }


    public function create()
    {
        return view('cms.accounting.first_grade.edit', [
            'method' => 'create',
            'formAction' => Route('cms.first_grade.create'),
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required',
                        'string',
                        'unique:App\Models\FirstGrade'
            ]
        ]);

        $latestFirstGradeCode = DB::table('acc_first_grade')
            ->select('code')
            ->orderByRaw('CONVERT(code, SIGNED) DESC')
            ->first();
        $newCode = GeneralLedger::generateCode($latestFirstGradeCode->code, '1');

        FirstGrade::create([
            'code' => strval($newCode),
            'name' => $request->input('name'),
            'has_next_grade' => 0,
        ]);
        return redirect(Route('cms.first_grade.index'));
    }
}
