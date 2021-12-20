<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImg;
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
            'images' => [],
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

        $request->validate([
            'files.*' => 'max:10000|mimes:jpg,jpeg,png,bmp',
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

        if ($request->hasfile('files')) {
            foreach ($request->file('files') as $file) {
                $imgData[] = $file->store('product_imgs');
            }
            ProductImg::createImgs($re['id'], $imgData);
        }

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
        $data = Product::where('id', $id)->get()->first();
        if (!$data) {
            return abort(404);
        }

        $data->active_sdate = $data->active_sdate ? date('Y-m-d', strtotime($data->active_sdate)) : null;
        $data->active_edate = $data->active_edate ? date('Y-m-d', strtotime($data->active_edate)) : null;

        $current_supplier = Supplier::getProductSupplier($id, true);

        return view('cms.commodity.product.basic_info', [
            'method' => 'edit',
            'formAction' => Route('cms.product.edit', ['id' => $id]),
            'users' => User::get(),
            'data' => $data,
            'suppliers' => Supplier::get(),
            'current_supplier' => $current_supplier,
            'categorys' => Category::get(),
            'images' => ProductImg::where('product_id', $id)->get(),
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
            'files.*' => 'max:5000|mimes:jpg,jpeg,png,bmp',
            'url' => ["unique:App\Models\Product,url,$id,id", 'nullable'],
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

        if ($request->hasfile('files')) {
            foreach ($request->file('files') as $file) {
                $imgData[] = $file->store('product_imgs');
            }
            ProductImg::createImgs($id, $imgData);
        }

        if (isset($d['del_image']) && $d['del_image']) {
            ProductImg::delImgs(explode(',', $d['del_image']));
        }

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
     * 編輯 - 編輯規格
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editStyle2($id)
    {
        return view('cms.commodity.product.styles-edit', [
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
