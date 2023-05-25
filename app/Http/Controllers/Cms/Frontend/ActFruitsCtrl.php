<?php

namespace App\Http\Controllers\Cms\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ActFruitsCtrl extends Controller
{
    public $SaleStatus = [
        0 => '販售中',
        13 => '已售罄',
        14 => '今年產季已過',
        1 => '1月開放預購',
        2 => '2月開放預購',
        3 => '3月開放預購',
        4 => '4月開放預購',
        5 => '5月開放預購',
        6 => '6月開放預購',
        7 => '7月開放預購',
        8 => '8月開放預購',
        9 => '9月開放預購',
        10 => '10月開放預購',
        11 => '11月開放預購',
        12 => '12月開放預購',
    ];
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

        return view('cms.frontend.act_fruits.list', [
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
        return view('cms.frontend.act_fruits.edit', [
            'method' => 'create',
            'saleStatus' => $this->SaleStatus
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
     * 水果分類設定
     *
     * @return \Illuminate\Http\Response
     */
    public function season()
    {
        return view('cms.frontend.act_fruits.season', [
            'method' => 'create',
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
        return view('cms.frontend.act_fruits.edit', [
            'method' => 'edit',
            'saleStatus' => $this->SaleStatus
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
