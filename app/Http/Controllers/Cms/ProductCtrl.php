<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
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

        return view('cms.commodity.product.list', [
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
            'suppliers' => Supplier::get(),
            'categorys' => Category::get(),
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

        if($request->hasfile('files')) {
            dd($request->file('files'));
            foreach($request->file('files') as $file)
            {
               // $name = $file->getClientOriginalName();
               // $file->move(public_path().'/uploads/', $name);  
               // $imgData[] = $name;  
            }
    
            
        }

        $request->validate([
            // 'files' => 'required|max:10000|mimes:png,jpg',
            'title' => 'required',
            'has_tax' => 'required',
            'active_sdate' => 'date|nullable',
            'active_edate' => 'date|nullable',
            'user_id' => 'required',
            'category_id' => 'required',
            'supplier' => 'required|array',
        ]);

        // $path = $request->file('file')->store('excel');

        $d = $request->all();
        $re = Product::createProduct($d['title'], $d['user_id'], $d['category_id'], $d['feature'], $d['url'], $d['slogan'], $d['active_sdate'], $d['active_edate'], $d['supplier'], $d['has_tax']);
        wToast('新增完畢');
        return redirect(route('cms.product.index'));
    }

    /**
     * 編輯 - 商品資訊
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //

        $current_supplier = Supplier::getProductSupplier($id, true);

        return view('cms.commodity.product.basic_info', [
            'method' => 'edit',
            'formAction' => Route('cms.product.edit', ['id' => $id]),
            'users' => User::get(),
            'data' => Product::where('id', $id)->get()->first(),
            'suppliers' => Supplier::get(),
            'current_supplier' => $current_supplier,
        ]);
    }

    /**
     * 編輯 - 商品資訊 儲存
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
            'supplier' => 'required|array',
        ]);

        $d = $request->all();

        Product::updateProduct($id, $d['title'], $d['user_id'], $d['category_id'], $d['feature'], $d['url'], $d['slogan'], $d['active_sdate'], $d['active_edate'], $d['supplier'], $d['has_tax']);
        wToast('儲存完畢');
        return redirect(route('cms.product.index'));

        //
    }

    /**
     * 編輯 - 規格款式
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editStyle($id)
    {
        return view('cms.commodity.product.styles', [
            'data' => Product::where('id', $id)->get()->first(),
        ]);
    }

    /**
     * 編輯 - 銷售控管
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editSale($id)
    {
        return view('cms.commodity.product.sales', [
            'data' => Product::where('id', $id)->get()->first(),
        ]);
    }

    /**
     * 編輯 - [網頁]商品介紹
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editWebDesc($id)
    {
        return view('cms.commodity.product.web_desciption', [
            'data' => Product::where('id', $id)->get()->first(),
        ]);
    }

    /**
     * 編輯 - [網頁]規格說明
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editWebSpec($id)
    {
        return view('cms.commodity.product.web_spec', [
            'data' => Product::where('id', $id)->get()->first(),
        ]);
    }

    /**
     * 編輯 - [網頁]運送方式
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editWebLogis($id)
    {
        return view('cms.commodity.product.web_logistics', [
            'data' => Product::where('id', $id)->get()->first(),
        ]);
    }

    /**
     * 編輯 - 設定
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editSetting($id)
    {
        return view('cms.commodity.product.settings', [
            'data' => Product::where('id', $id)->get()->first(),
        ]);
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
