<?php

namespace App\Http\Controllers\Cms\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserOrganize;
use Illuminate\Http\Request;

class OrganizeCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      
        return view('cms.settings.organize.list', [
            'dataList' => UserOrganize::dataList(),
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
        $data = UserOrganize::where('id', $id)->get()->first();

        if (!$data) {
            return abort(404);
        }

        return view('cms.settings.organize.edit', [
            'data' => $data,
            'formAction' => route('cms.organize.edit', ['id' => $id]),
            'users' => User::get(),
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
        $user_id = $request->input('user_id');
        if ($user_id) {
            $update = ['user_id' => $user_id];
        } else {
            $update = ['user_id' => null];
        }

        UserOrganize::where('id', $id)->update($update);

        wToast('更新完成');

        return redirect(route('cms.organize.index'));
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
