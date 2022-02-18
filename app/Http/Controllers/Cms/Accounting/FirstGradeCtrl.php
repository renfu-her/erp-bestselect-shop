<?php

namespace App\Http\Controllers\Cms\Accounting;

use App\Http\Controllers\Controller;
use App\Models\FirstGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class FirstGradeCtrl extends Controller
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

        $dataList =  FirstGrade::paginate($data_per_page)->appends($query);

        return view('cms.accounting.first_grade.list', [
            'dataList' => $dataList,
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
        return view('cms.accounting.first_grade.edit', [
            'method' => 'create',
            'formAction' => Route('cms.first_grade.create'),
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
            'name' => ['required',
                        'string',
                        'unique:App\Models\FirstGrade'
            ]
        ]);

        FirstGrade::create([
            'name' => $request->input('name')
        ]);
        return redirect(Route('cms.first_grade.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FirstGrade  $firstGrade
     *
     * @return \Illuminate\Http\Response
     */
    public function show(FirstGrade $firstGrade)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FirstGrade  $firstGrade
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(FirstGrade $firstGrade)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FirstGrade  $firstGrade
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FirstGrade $firstGrade)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FirstGrade  $firstGrade
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(FirstGrade $firstGrade)
    {
        //
    }
}
