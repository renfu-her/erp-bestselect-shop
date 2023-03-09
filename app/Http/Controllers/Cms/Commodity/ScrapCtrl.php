<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Http\Controllers\Controller;
use App\Models\GeneralLedger;
use App\Models\PcsScrapItem;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class ScrapCtrl extends Controller
{
    public function index(Request $request)
    {
        $searchParam = [];
        $query = $request->query();
        $searchParam['scrap_sn'] = Arr::get($query, 'scrap_sn', null);

        $data_per_page = Arr::get($query, 'data_per_page', 100);
        $searchParam['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page', 100));
        $dataList = PcsScrapItem::getDataList($searchParam)
            ->paginate($searchParam['data_per_page'])->appends($query);

        return view('cms.commodity.scrap.list', [
            'dataList' => $dataList,
            'data_per_page' => $data_per_page,
            'searchParam' => $searchParam
        ]);
    }

    public function create(Request $request)
    {
        $rsp_arr = [];
        $rsp_arr['dlv_other_items'] = [];
        $rsp_arr['method'] = 'create';
        $rsp_arr['formAction'] = Route('cms.scrap.create');
        $total_grades = GeneralLedger::total_grade_list();
        $rsp_arr['total_grades'] = $total_grades;

        return view('cms.commodity.scrap.edit', $rsp_arr);
    }

    public function store(Request $request)
    {
        dd($request->all(), Auth::user());
    }

    public function edit(Request $request, $id)
    {

    }

    public function update(Request $request, $id)
    {

    }

    public function destroy(Request $request, $id)
    {
        dd('destroy', $id);
    }

    public function printScrap(Request $request, $id)
    {

    }
}

