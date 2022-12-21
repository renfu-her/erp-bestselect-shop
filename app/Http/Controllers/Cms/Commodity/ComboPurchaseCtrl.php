<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductStyle;
use App\Models\ProductStyleCombo;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ComboPurchaseCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
       
        $query = $request->query();
        $keyword = Arr::get($query, 'keyword', null);
        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $negative_stock = Arr::get($query, 'negative_stock', null);

        $options = ['negative_stock' => $negative_stock];
        $dataList = Product::productStyleList($keyword, 'c', null, $options)
            ->paginate($data_per_page)->appends($query);

        return view('cms.commodity.combo_purchase.list', [
            'dataList' => $dataList,
            'query' => $query,
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        //
        $style = ProductStyle::where('id', $id)->get()->first();
        if (!$style) {
            return abort(404);
        }
        $combos = ProductStyleCombo::comboList($id)->get();

        $product = self::product_data($style->product_id);
        return view('cms.commodity.combo_purchase.edit', [
            'product' => $product,
            'style' => $style,
            'combos' => $combos,
            'breadcrumb_data' => ['product' => $product, 'style' => $style],
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
        // dd($_POST);
        $request->validate([
            'qty' => 'required',
        ]);

        $check_stock = isset($_POST['check_stock']) ? true : false;

        $qty = $request->input('qty');
        $re = ProductStock::comboProcess($id, $qty, $check_stock);

        if (!$re['success']) {
            return redirect()->back()->withErrors(['qty' => $re['error_msg']]);
        }

        wToast("儲存成功");
        return redirect()->back();

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

    public static function correction(){
        //dd('aa');

        ProductStyleCombo::correction();
        // dd('vv');
        wToast('校正回歸完成');

        return redirect()->back();
    }

}
