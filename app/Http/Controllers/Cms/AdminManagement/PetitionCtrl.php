<?php

namespace App\Http\Controllers\Cms\AdminManagement;

use App\Http\Controllers\Controller;
use App\Models\Petition;
use Illuminate\Http\Request;

class PetitionCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
       // Petition::waitAuditlist(2);

        $dataList = Petition::dataList()->paginate(100);

        foreach ($dataList as $data) {
            $data->users = $data->users ? json_decode($data->users) : [];
            
        }


        return view('cms.admin_management.petition.list', [
            'dataList' => $dataList,
            'data_per_page' => 100,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
        Petition::createPetition($request->user()->id, 'a', 'b');
        return view('cms.admin_management.petition.edit', [
            'method' => 'create',
            'formAction' => route('cms.petition.create'),
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
}
