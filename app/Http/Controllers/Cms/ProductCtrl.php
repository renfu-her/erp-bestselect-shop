<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImg;
use App\Models\ProductSpec;
use App\Models\ProductSpecItem;
use App\Models\ProductStyle;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;

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
        $specList = ProductSpec::specList($id);
        $styles = ProductStyle::where('product_id', $id)->get()->toArray();
        $init_styles = [];
        if (count($styles) == 0) {
            $init_styles = ProductStyle::createInitStyles($id);
        }


        return view('cms.commodity.product.styles', [
            'data' => Product::where('id', $id)->get()->first(),
            'specList' => $specList,
            'styles' => $styles,
            'initStyles' => $init_styles,
        ]);
    }

    public function storeStyle(Request $request, $id)
    {

        $d = $request->all();
        $specCount = DB::table('prd_product_spec')->where('product_id', $id)->count();
        // dd($d);
        if (isset($d['nsk_style_id'])) {
            foreach ($d['nsk_style_id'] as $key => $value) {
                $updateData = [];
                for ($i = 1; $i <= $specCount; $i++) {
                    if (isset($d["nsk_spec$i"][$key])) {
                        $updateData["spec_item${i}_id"] = $d['nsk_spec' . $i][$key];
                    }
                }
                $updateData['sold_out_event'] = $d['nsk_sold_out_event'][$key];

                ProductStyle::where('id', $value)->whereNull('sku')->update($updateData);
            }
        }

        if (isset($d['sk_style_id'])) {
            foreach ($d['sk_style_id'] as $key => $value) {
                $updateData = [];
                $updateData['sold_out_event'] = $d['sk_sold_out_event'][$key];
                ProductStyle::where('id', $value)->whereNotNull('sku')->update($updateData);
            }
        }
        if (isset($d['active_id'])) {
            ProductStyle::activeStyle($id, $d['active_id']);
        }

        if (isset($d['n_sold_out_event'])) {
            $newItemCount = count($d['n_sold_out_event']);
            for ($i = 0; $i < $newItemCount; $i++) {
                $updateData = [];
                for ($j = 1; $j <= $specCount; $j++) {
                    if (isset($d["n_spec$j"][$i])) {
                        $updateData["spec_item${j}_id"] = $d['n_spec' . $j][$i];
                    }
                }

                ProductStyle::createStyle($id, $updateData);

            }
        }

        if ($d['del_id']) {
            ProductStyle::whereIn('id', explode(',', $d['del_id']))->where('product_id', $id)->delete();
        }
        wToast('修改完成');
        return redirect(route('cms.product.edit-style', ['id' => $id]));

    }

    public function createAllSku(Request $request, $id)
    {
        $styles = ProductStyle::where('product_id', $id)->whereNull('sku')->select('id')->get()->toArray();

        foreach ($styles as $style) {
            ProductStyle::createSku($id, $style['id']);
        }

        if (count($styles) > 0) {
            Product::where('id', $id)->update(['spec_locked' => 1]);
        }

        wToast('sku產生完成');
        return redirect(route('cms.product.edit-style', ['id' => $id]));
    }

    /**
     * 編輯 - 編輯規格
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editSpec($id)
    {

        return view('cms.commodity.product.spec-edit', [
            'data' => Product::where('id', $id)->get()->first(),
            'specs' => ProductSpec::get()->toArray(),
            'currentSpec' => ProductSpec::specList($id),
        ]);
    }

    public function storeSpec(Request $request, $id)
    {
        $d = $request->all();

        for ($i = 0; $i < 3; $i++) {
            if (isset($d["spec" . $i])) {
                Product::setProductSpec($id, $d["spec" . $i]);
                if (isset($d["item" . $i]) && is_array($d["item" . $i])) {
                    foreach ($d["item" . $i] as $item) {
                        ProductSpecItem::createItems($id, $d["spec" . $i], $item);
                    }
                }
            }
        }

        return redirect(Route('cms.product.edit-style', ['id' => $id]));
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
