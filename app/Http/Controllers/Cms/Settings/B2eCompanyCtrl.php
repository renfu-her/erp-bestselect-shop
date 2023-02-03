<?php

namespace App\Http\Controllers\Cms\Settings;

use App\Http\Controllers\Controller;
use App\Models\B2eCompany;
use App\Models\SaleChannel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class B2eCompanyCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $request->query();
        $cond['keyword'] = Arr::get($query, 'keyword');

        $dataList = B2eCompany::dataList($cond['keyword'])->paginate(100)->appends($query);
        return view('cms.settings.b2e.list', [
            'dataList' => $dataList,
            'data_per_page' => 10,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('cms.settings.b2e.edit', [
            'method' => 'create',
            'salechannels' => SaleChannel::get(),
            'users' => User::get(),
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
        //   dd($_POST);
        // B2eCompany
        $request->validate([
            'title' => 'required',
            'short_title' => 'required',
            'vat_no' => 'required',
            'contact_person' => 'required',
        ], $_POST);

        $d = $request->all();

        $imgData = null;
        if ($request->hasfile('img')) {
            $imgData = $request->file('img')->store('idx_banner/');
        }

        B2eCompany::create([
            "title" => $d['title'],
            "short_title" => $d['short_title'],
            "vat_no" => $d['vat_no'],
            "tel" => $d['tel'],
            "ext" => $d['ext'],
            "contact_person" => $d['contact_person'],
            "contact_tel" => $d['contact_tel'],
            "contact_email" => $d['contact_email'],
            "contract_sdate" => $d['contract_sdate'],
            "contract_edate" => $d['contract_edate'],
            'salechannel_id' => $d['salechannel_id'],
            'user_id' => $d['user_id'],
            'img' => $imgData,
        ]);

        wToast('新增完成');

        return redirect(route('cms.b2e-company.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        $data = B2eCompany::where('id', $id)->get()->first();
        if (!$data) {
            return abort(404);
        }

        return view('cms.settings.b2e.edit', [
            'method' => 'edit',
            'salechannels' => SaleChannel::get(),
            'users' => User::get(),
            'data' => $data,
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
        //
        $request->validate([
            'title' => 'required',
            'short_title' => 'required',
            'vat_no' => 'required',
            'contact_person' => 'required',
        ], $_POST);

        $d = $request->all();
        $imgData = null;
        if ($request->hasfile('img')) {
            $imgData = $request->file('img')->store('idx_banner/');
        }
        B2eCompany::where('id', $id)->update([
            "title" => $d['title'],
            "short_title" => $d['short_title'],
            "vat_no" => $d['vat_no'],
            "tel" => $d['tel'],
            "ext" => $d['ext'],
            "contact_person" => $d['contact_person'],
            "contact_tel" => $d['contact_tel'],
            "contact_email" => $d['contact_email'],
            "contract_sdate" => $d['contract_sdate'],
            "contract_edate" => $d['contract_edate'],
            'salechannel_id' => $d['salechannel_id'],
            'user_id' => $d['user_id'],
            'img' => $imgData,
        ]);

        wToast('更新完成');

        return redirect(route('cms.b2e-company.index'));
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
}
