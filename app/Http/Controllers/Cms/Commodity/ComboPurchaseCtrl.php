<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductStyle;
use App\Models\ProductStyleCombo;

class ComboPurchaseCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('cms.commodity.comboPurchase.list', [
            'data_per_page' => 10
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
    public function edit($id, $sid)
    {
        $product = self::product_data($id);
        $style = ProductStyle::where('id', $sid)->get()->first();
        $combos = ProductStyleCombo::comboList($sid)->get();
        return view('cms.commodity.comboPurchase.edit', [
            'product' => $product,
            'style' => $style,
            'combos' => $combos,
            'breadcrumb_data' => ['product' => $product, 'style' => $style]
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
