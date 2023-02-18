<?php

namespace App\Http\Controllers\Cms\Settings;

use App\Http\Controllers\Controller;
use App\Models\ErpTravel;
use Illuminate\Http\Request;

class ErpTravelCtrl extends Controller
{

    private $radioOptions = ['0' => "關閉", '1' => "啟用"];
    /**
     * Display a listing of the resource.Ｆ
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //

        $re = ErpTravel::getUsers();
        if (isset($re['status'])) {{
            dd($re);
        }}

        return view('cms.settings.erp_travel.list', [
            "dataList" => $re,
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
        return view('cms.settings.erp_travel.edit', [
            'method' => 'create',
            'formAction' => route('cms.erp-travel.create'),
            'radioOptions' => $this->radioOptions,
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

        //
        $re = ErpTravel::updateData($request->input('login_name'), $request->all());
        //  dd($_POST);

        if ($re['status'] == '0') {
            wToast('存擋完成');
        } else {
            wToast('失敗');
        }

        return redirect(route('cms.erp-travel.index'));
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
        $data = ErpTravel::getUsers($id);

        if (!$data) {
            return abort(400);
        }
        //  dd($data);
        return view('cms.settings.erp_travel.edit', [
            "data" => $data[0],
            'method' => 'edit',
            'formAction' => route('cms.erp-travel.edit', ['id' => $id]),
            'radioOptions' => $this->radioOptions,
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
        $re = ErpTravel::updateData($request->input('login_name'), $request->all());
        //  dd($_POST);

        if ($re['status'] == '0') {
            wToast('存擋完成');
        } else {
            wToast('失敗');
        }

        return redirect(route('cms.erp-travel.index'));
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
