<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImg;
use App\Models\ProductSpec;
use App\Models\ProductSpecItem;
use App\Models\ProductStyle;
use App\Models\ProductStyleCombo;
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

        //   dd(Product::productList()->get());

        return view('cms.commodity.product.list', [
            'dataList' => Product::productList()->paginate(10),
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
            'type' => 'required|in:c,p',
            // 'url'=>'unique:App\Models\Product'
        ]);

        // $path = $request->file('file')->store('excel');

        $d = $request->all();
        $re = Product::createProduct($d['title'], $d['user_id'], $d['category_id'], $d['type'], $d['feature'], $d['url'], $d['slogan'], $d['active_sdate'], $d['active_edate'], $d['supplier'], $d['has_tax']);

        if ($request->hasfile('files')) {
            foreach ($request->file('files') as $file) {
                $imgData[] = $file->store('product_imgs/' . $re['id']);
            }
            ProductImg::createImgs($re['id'], $imgData);
        }
        if ($d['type'] == 'p') {
            $url = 'cms.product.edit-style';
        } else {
            $url = 'cms.product.edit-combo';
        }

        wToast('新增完畢');
        return redirect(route($url, ['id' => $re['id']]));
    }

    /**
     * 取得商品資料共用
     * @return object
     */

    private static function product_data($id, $full = false)
    {
        $data = Product::where('id', $id)->get()->first();
        if (!$full) {
            $data->select('id', 'title', 'type');
        }
        if (!$data) {
            return abort(404);
        }
        return $data;
    }

    /**
     * 編輯 - 商品資訊
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $product = self::product_data($id, true);

        $product->active_sdate = $product->active_sdate ? date('Y-m-d', strtotime($product->active_sdate)) : null;
        $product->active_edate = $product->active_edate ? date('Y-m-d', strtotime($product->active_edate)) : null;

        $current_supplier = Supplier::getProductSupplier($id, true);

        return view('cms.commodity.product.basic_info', [
            'method' => 'edit',
            'formAction' => Route('cms.product.edit', ['id' => $id]),
            'users' => User::get(),
            'product' => $product,
            'suppliers' => Supplier::get(),
            'current_supplier' => $current_supplier,
            'categorys' => Category::get(),
            'images' => ProductImg::where('product_id', $id)->get(),
            'breadcrumb_data' => $product,
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
            //  'url' => ["unique:App\Models\Product,url,$id,id", 'nullable'],
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
                $imgData[] = $file->store('product_imgs/' . $id);
            }
            ProductImg::createImgs($id, $imgData);
        }

        if (isset($d['del_image']) && $d['del_image']) {
            ProductImg::delImgs(explode(',', $d['del_image']));
        }

        wToast('儲存完畢');
        return redirect()->back();

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
        $product = self::product_data($id);
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
            'product' => $product,
            'breadcrumb_data' => $product,
        ]);
    }

    public function storeStyle(Request $request, $id)
    {

        $specCount = DB::table('prd_product_spec')->where('product_id', $id)->count();
        $d = $request->all();
        if (isset($d['nsk_style_id'])) {
            foreach ($d['nsk_style_id'] as $key => $value) {
                $updateData = [];
                $itemIds = [];
                for ($i = 1; $i <= $specCount; $i++) {
                    if (isset($d["nsk_spec$i"][$key])) {
                        // $updateData["spec_item${i}_id"] = $d['nsk_spec' . $i][$key];
                        $itemIds[] = $d['nsk_spec' . $i][$key];
                    }
                }
                $updateData['sold_out_event'] = $d['nsk_sold_out_event'][$key];

                //  ProductStyle::where('id', $value)->whereNull('sku')->update($updateData);
                ProductStyle::updateStyle($value, $id, $itemIds, $updateData);
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

        if (isset($d['add_sku']) && $d['add_sku']) {
            ProductStyle::createSkuByProductId($id);
        }

        wToast('修改完成');
        return redirect(route('cms.product.edit-style', ['id' => $id]));

    }

    // 產生SKU
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
        // return redirect(route('cms.product.edit-style', ['id' => $id]));
        return redirect()->back();
    }

    /**
     * 編輯 - 規格款式 - 編輯規格
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editSpec($id)
    {
        $product = self::product_data($id);

        return view('cms.commodity.product.spec-edit', [
            'data' => Product::where('id', $id)->get()->first(),
            'specs' => ProductSpec::get()->toArray(),
            'currentSpec' => ProductSpec::specList($id),
            'product' => $product,
            'breadcrumb_data' => $product,
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
        $product = self::product_data($id);
        $specList = ProductSpec::specList($id);
        $styles = ProductStyle::where('product_id', $id)->get()->toArray();
        
        return view('cms.commodity.product.sales', [
            'product' => $product,
            'specList' => $specList,
            'styles' => $styles,
            'breadcrumb_data' => $product,
        ]);
    }
    /**
     * 編輯 - 銷售控管 - 庫存管理
     *
     * @param  int  $id 商品id
     * @param  int  $sid 款式id
     * @return \Illuminate\Http\Response
     */
    public function editStock($id, $sid)
    {
        $product = self::product_data($id);
        $style = ProductStyle::where('id', $sid)->get()->first();
        // dd($style);
        return view('cms.commodity.product.sales-stock', [
            'product' => $product,
            'style' => $style,
            'breadcrumb_data' => $product,
        ]);
    }
    /**
     * 編輯 - 銷售控管 - 價格管理
     *
     * @param  int  $id 商品id
     * @param  int  $sid 款式id
     * @return \Illuminate\Http\Response
     */
    public function editPrice($id, $sid)
    {
        $product = self::product_data($id);
        $style = ProductStyle::where('id', $sid)->get()->first();
        
        return view('cms.commodity.product.sales-price', [
            'product' => $product,
            'style' => $style,
            'breadcrumb_data' => $product,
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
        $product = self::product_data($id);
        return view('cms.commodity.product.web_desciption', [
            'product' => $product,
            'breadcrumb_data' => $product,
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
        $product = self::product_data($id);
        return view('cms.commodity.product.web_spec', [
            'product' => $product,
            'breadcrumb_data' => $product,
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
        $product = self::product_data($id);
        return view('cms.commodity.product.web_logistics', [
            'product' => $product,
            'breadcrumb_data' => $product,
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
        $product = self::product_data($id);
        return view('cms.commodity.product.settings', [
            'product' => $product,
            'breadcrumb_data' => $product,
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

    /**
     * 組合包管理
     *
     * @return \Illuminate\Http\Response
     */
    public function editCombo($id)
    {
        $product = self::product_data($id);
        $styles = ProductStyle::where('product_id', $id)->get()->toArray();
        // dd($styles);
        return view('cms.commodity.product.combo', [
            'product' => $product,
            'styles' => $styles,
            'breadcrumb_data' => $product,
        ]);
    }

    public function updateCombo(Request $request, $id)
    {
        $d = $request->all();
        if (isset($d['sid'])) {
            for ($i = 0; $i < count($d['sid']); $i++) {
                if (isset($d['sold_out_event'][$i])) {
                    ProductStyle::where('id', $d['sid'][$i])->update(['sold_out_event' => $d['sold_out_event'][$i]]);
                }
            }
        }

        if (isset($d['active_id'])) {
            ProductStyle::activeStyle($id, $d['active_id']);
        }

        if (isset($d['del_id']) && $d['del_id']) {
            ProductStyle::whereIn('id', explode(',', $d['del_id']))->whereNull('sku')->delete();
        }

        if (isset($d['add_sku']) && $d['add_sku']) {
            ProductStyle::createSkuByProductId($id);
        }

        wToast('儲存完畢');
        return redirect()->back();

    }

    /**
     * 編輯組合包
     *
     * @return \Illuminate\Http\Response
     */
    public function editComboProd($id, $sid)
    {
        $style = ProductStyle::where('id', $sid)->get()->first();
        $product = self::product_data($id);
        return view('cms.commodity.product.combo-edit', [
            'data' => $style,
            'product' => $product,
            'combos' => ProductStyleCombo::comboList($sid)->get(),
            'method' => 'create',
            'formAction' => Route('cms.product.edit-combo-prod', ['id' => $id, 'sid' => $sid]),
            'breadcrumb_data' => ['product' => $product,
                'style' => $style],
        ]);
    }

    /**
     * 編輯組合包
     *
     * @return \Illuminate\Http\Response
     */
    public function updateComboProd(Request $request, $id, $sid)
    {

        if (ProductStyle::where('id', $sid)->whereNotNull('sku')->get()->first()) {
            return redirect()->back();
        }

        $request->validate([
            'title' => 'required',
            'style_id' => 'array',
            'ps_qty' => 'array',
            'o_style_id' => 'array',
            'o_ps_qty' => 'array',
        ]);
        $d = request()->all();

        if (isset($d['style_id'])) {
            for ($i = 0; $i < count($d['style_id']); $i++) {
                if ($d['ps_qty'][$i]) {
                    ProductStyleCombo::createCombo($sid, $d['style_id'][$i], $d['ps_qty'][$i]);
                }
            }
        }

        if (isset($d['o_style_id'])) {
            for ($i = 0; $i < count($d['o_style_id']); $i++) {
                if ($d['o_ps_qty'][$i]) {
                    ProductStyleCombo::where('id', $d['o_style_id'][$i])
                        ->update(['qty' => $d['o_ps_qty'][$i]]);
                }
            }
        }

        ProductStyle::where('id', $sid)->update(['title' => $d['title']]);

        if (isset($d['del_item_id'])) {
            ProductStyleCombo::whereIn('id', explode(',', $d['del_item_id']))->delete();
        }

        wToast('儲存完畢');
        return redirect(Route('cms.product.edit-combo', ['id' => $id]));

    }

    /**
     * 新增組合包
     *
     * @return \Illuminate\Http\Response
     */
    public function createComboProd($id)
    {
        $product = self::product_data($id);
        return view('cms.commodity.product.combo-edit', [
            'product' => $product,
            'combos' => [],
            'method' => 'create',
            'formAction' => Route('cms.product.create-combo-prod', ['id' => $id]),
            'breadcrumb_data' => $product,
        ]);
    }

    /**
     * 新增組合包
     *
     * @return \Illuminate\Http\Response
     */
    public function storeComboProd(Request $request, $id)
    {

        // dd($_POST);
        $request->validate([
            'title' => 'required',
            'style_id' => 'array',
            'ps_qty' => 'array',
        ]);
        $d = request()->all();

        $sid = ProductStyle::createComboStyle($id, $d['title'], 1);
        if (isset($d['style_id'])) {
            for ($i = 0; $i < count($d['style_id']); $i++) {
                if ($d['ps_qty'][$i]) {
                    ProductStyleCombo::createCombo($sid, $d['style_id'][$i], $d['ps_qty'][$i]);
                }
            }
        }

        return redirect(Route('cms.product.edit-combo', ['id' => $id]));

    }
}
