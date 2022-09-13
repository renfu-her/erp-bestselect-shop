<?php

namespace App\Http\Controllers\Cms\Accounting;

use App\Http\Controllers\Controller;
use App\Models\IncomeStatement;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class IncomeStatementCtrl extends Controller
{
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


    public function create()
    {
        return view('cms.accounting.income_statement.edit', [
            'method' => 'create',
            'formAction' => Route('cms.income_statement.create'),
        ]);
    }


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
}
