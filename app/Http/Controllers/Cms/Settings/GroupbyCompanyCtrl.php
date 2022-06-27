<?php

namespace App\Http\Controllers\Cms\Settings;

use App\Http\Controllers\Controller;
use App\Models\GroupbyCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class GroupbyCompanyCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        // GroupbyCompany::

        $query = $request->query();
        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;
        $dataList = GroupbyCompany::where('parent_id', '0')->paginate($data_per_page)->appends($query);

        return view('cms.settings.groupby_company.list', [
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
        return view('cms.settings.groupby_company.edit', [
            'method' => 'create',
            'action' => Route('cms.groupby-company.create'),
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

        $request->validate(self::vali());

        $d = $request->all();

        $child = [];
        if (isset($d['n_title'])) {
            foreach ($d['n_title'] as $key => $value) {
                $child[] = [
                    'title' => $value,
                    'code' => $d['n_code'][$key],
                    'active' => Arr::get($d, 'n_active_' . $key, '0'),
                ];
            }
        }

        $is_active = Arr::get($d, 'active', '0');
        $re = GroupbyCompany::createMain($d['title'], $is_active, $child);

        if ($re['success'] == '1') {
            wToast('新增完成');
            return redirect(route('cms.groupby-company.index'));
        }

        $err = self::codeErrorHandler($re['errors']);
       
        return redirect()->back()->withErrors($err);
        //  dd($_POST);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {

        $mainData = GroupbyCompany::where('id', $id)->get()->first();
        $childData = GroupbyCompany::where('parent_id', $id)->get();
        return view('cms.settings.groupby_company.edit', [
            'method' => 'edit',
            'mainData' => $mainData,
            'childData' => $childData,
            'action' => Route('cms.groupby-company.edit', ['id' => $id]),
        ]);
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

        $vali = array_merge(self::vali(), ['o_active' => ['array'],
            'o_title' => ['array'],
            'o_title.*' => ['required'],
            'o_code' => ['array'],
            'o_id' => ['array'],
            'o_id.*' => ['required']]);
        //    dd($vali);
        $request->validate($vali);

        $d = $request->all();
        $is_active = Arr::get($d, 'active', '0');

        $child = [];
        if (isset($d['n_title'])) {
            foreach ($d['n_title'] as $key => $value) {
                $child[] = [
                    'title' => $value,
                    'code' => $d['n_code'][$key],
                    'active' => Arr::get($d, 'n_active_' . $key, '0'),
                    // 'active' => $d['n_active'][$key] ? $d['n_active'][$key] : '0',
                ];
            }
        }

        $oChild = [];
        if (isset($d['o_title'])) {
            foreach ($d['o_title'] as $key => $value) {
                $oChild[] = [
                    'title' => $value,
                    'code' => $d['o_code'][$key],
                    'id' => $d['o_id'][$key],
                    'active' => Arr::get($d, 'o_active_' . $key, '0'),
                    // 'active' => $d['n_active'][$key] ? $d['n_active'][$key] : '0',
                ];
            }
        }

        $re = GroupbyCompany::updateMain($id, $d['title'], $is_active, $child, $oChild);

        if ($re['success'] == '1') {
            wToast('新增完成');
            return redirect(route('cms.groupby-company.index'));
        }
        $err = self::codeErrorHandler($re['errors']);
        /*
        foreach ($re['errors'] as $value) {
        $err[$value['type'] . "_code." . $value['index']] = $value['type'] . "_code." . $value['index'] . ' 已經存在';
        }
         */
        return redirect()->back()->withErrors($err);
        //  dd($re);
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

    private function vali()
    {
        return [
            'title' => ['required', 'string'],
            'n_active' => ['array'],
            'n_title' => ['array'],
            'n_title.*' => ['required'],
            'n_code' => ['array'],
            'n_code.*' => ['required', 'unique:App\Models\GroupbyCompany,code'],
        ];
    }

    private function codeErrorHandler($errors)
    {
        $err = [];
        foreach ($errors as $value) {
            $err[$value['type'] . "_code." . $value['index']] = $value['type'] . "_code." . $value['index'] . ' 已經存在';
        }

        return $err;
    }
}
