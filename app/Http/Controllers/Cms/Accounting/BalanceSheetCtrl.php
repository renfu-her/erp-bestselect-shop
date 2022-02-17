<?php

namespace App\Http\Controllers\Cms\Accounting;

use App\Http\Controllers\Controller;
use App\Models\BalanceSheet;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class BalanceSheetCtrl extends Controller
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

        $dataList =  BalanceSheet::paginate($data_per_page)->appends($query);

        return view('cms.accounting.balance_sheet.list', [
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
        return view('cms.accounting.balance_sheet.edit', [
            'method' => 'create',
            'formAction' => Route('cms.balance_sheet.create'),
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
                        'unique:App\Models\BalanceSheet'
            ]
        ]);

        BalanceSheet::create([
            'name' => $request->input('name')
        ]);
        return redirect(Route('cms.balance_sheet.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BalanceSheet  $balanceSheet
     * @return \Illuminate\Http\Response
     */
    public function show(BalanceSheet $balanceSheet)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BalanceSheet  $balanceSheet
     * @return \Illuminate\Http\Response
     */
    public function edit(BalanceSheet $balanceSheet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BalanceSheet  $balanceSheet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BalanceSheet $balanceSheet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BalanceSheet  $balanceSheet
     * @return \Illuminate\Http\Response
     */
    public function destroy(BalanceSheet $balanceSheet)
    {
        //
    }
}
