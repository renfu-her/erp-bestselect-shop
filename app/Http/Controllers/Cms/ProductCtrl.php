<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class ProductCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return view('cms.commodity.product.main', [
            'dataList' => Product::paginate(10),
            'data_per_page' => 10]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('cms.commodity.product.basic_info', [
            'method' => 'create',
            'formAction' => Route('cms.product.create'),
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
        $request->validate([
            //  'file' => 'required|max:10000|mimes:xlsx,xls',
            'title' => 'required',
            'has_tax' => 'required',
            'active_sdate' => 'date|nullable',
            'active_edate' => 'date|nullable',
            'user_id' => 'required',
            'category_id' => 'required',
        ]);

        $d = $request->all();
        Product::createProduct($d['title'], $d['user_id'], $d['category_id'], $d['feature'], $d['url'], $d['slogan'], $d['active_sdate'], $d['active_edate'], $d['has_tax']);
        wToast('新增完畢');
        return redirect(route('cms.product.index'));
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
        return view('cms.commodity.product.basic_info', [
            'method' => 'edit',
            'formAction' => Route('cms.product.edit', ['id' => $id]),
            'users' => User::get(),
            'data' => Product::where('id', $id)->get()->first(),
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
        $request->validate([
            //  'file' => 'required|max:10000|mimes:xlsx,xls',
            'title' => 'required',
            'has_tax' => 'required',
            'active_sdate' => 'date|nullable',
            'active_edate' => 'date|nullable',
            'user_id' => 'required',
            'category_id' => 'required',
        ]);

        $d = $request->all();
        Product::updateProduct($d['title'], $d['user_id'], $d['category_id'], $d['feature'], $d['url'], $d['slogan'], $d['active_sdate'], $d['active_edate'], $d['has_tax']);
        wToast('儲存完畢');
        return redirect(route('cms.product.index'));

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
