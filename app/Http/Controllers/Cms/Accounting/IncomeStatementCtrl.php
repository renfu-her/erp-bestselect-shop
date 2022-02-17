<?php

namespace App\Http\Controllers\Cms\Accounting;

use App\Http\Controllers\Controller;
use App\Models\IncomeStatement;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class IncomeStatementCtrl extends Controller
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

        $dataList =  IncomeStatement::paginate($data_per_page)->appends($query);

        return view('cms.accounting.income_statement.list', [
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
        return view('cms.accounting.income_statement.edit', [
            'method' => 'create',
            'formAction' => Route('cms.income_statement.create'),
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
                       'unique:App\Models\IncomeStatement'
            ]
        ]);

        IncomeStatement::create([
            'name' => $request->input('name')
        ]);
        return redirect(Route('cms.income_statement.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\IncomeStatement  $incomeStatement
     * @return \Illuminate\Http\Response
     */
    public function show(IncomeStatement $incomeStatement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\IncomeStatement  $incomeStatement
     * @return \Illuminate\Http\Response
     */
    public function edit(IncomeStatement $incomeStatement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\IncomeStatement  $incomeStatement
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, IncomeStatement $incomeStatement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\IncomeStatement  $incomeStatement
     * @return \Illuminate\Http\Response
     */
    public function destroy(IncomeStatement $incomeStatement)
    {
        //
    }
}
