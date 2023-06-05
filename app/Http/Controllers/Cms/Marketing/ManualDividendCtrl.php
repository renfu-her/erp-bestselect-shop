<?php

namespace App\Http\Controllers\Cms\Marketing;

use App\Enums\Discount\DividendCategory;
use App\Http\Controllers\Controller;
use App\Imports\DividendImport;
use App\Models\ManualDividend;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ManualDividendCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $dataList = ManualDividend::dataList()
            ->orderBy('md.created_at', 'DESC')
            ->paginate(100);

        return view('cms.marketing.manual_dividend.list', [
            'dataList' => $dataList,
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

        return view('cms.marketing.manual_dividend.edit', [
            'formAction' => Route('cms.manual-dividend.create'),
            'dividendCategory' => DividendCategory::getValueWithDesc(),
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
        //
        $request->validate([
            'file' => 'max:5000000|mimes:xls,xlsx|required',
            'category' => 'required',

        ]);

        $d = $request->all();
        DB::beginTransaction();

        $id = ManualDividend::create([
            'user_id' => $request->user()->id,
            'category' => $d['category'],
            'category_title' => DividendCategory::fromValue($d['category'])->description,
        ])->id;

        Excel::import(new DividendImport($id, $d['category']), $request->file('file'));

        DB::commit();

        return redirect(Route('cms.manual-dividend.show', ['id' => $id], true));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = ManualDividend::dataList()->where('md.id', $id)->get()->first();
        if (!$data) {
            return abort(404);
        }

        $log = DB::table('dis_manual_dividend_log')->where('manual_dividend_id', $id)->get();
        
        return view('cms.marketing.manual_dividend.show', [
            'data' => $data,
            'log' => $log,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function sample(Response $response)
    {
        //
        $file = public_path() . "/excel/dividend.xlsx";

        $headers = array(
            'Content-Type: application/vnd.ms-excel',
        );

        return response()->download($file, 'dividend.xlsx', $headers);
    }
}
