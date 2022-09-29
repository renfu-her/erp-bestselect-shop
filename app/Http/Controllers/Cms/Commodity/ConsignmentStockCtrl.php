<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Delivery\Event;
use App\Http\Controllers\Controller;
use App\Models\CsnOrder;
use App\Models\Depot;
use App\Models\DepotProduct;
use App\Models\Product;
use App\Models\ProductStyle;
use App\Models\PurchaseLog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ConsignmentStockCtrl extends Controller
{
    //寄倉庫存
    public function stocklist(Request $request) {
        $query = $request->query();
        $data_per_page = Arr::get($query, 'data_per_page', 100);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 100;

        $depot_id = Arr::get($query, 'depot_id', 1);

        $queryDepotProduct = DepotProduct::ProductCsnExistInboundList($depot_id);

        $queryDepotProduct = $queryDepotProduct->paginate($data_per_page)->appends($query);

        return view('cms.commodity.consignment_stock.stock', [
            'dataList' => $queryDepotProduct
            , 'data_per_page' => $data_per_page
            , 'depotList' => Depot::all()
            , 'depot_id' => $depot_id
        ]);
    }

    public function historyStockDetailLog(Request $request, $depot_id, $id) {
        $query = $request->query();
        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;

        $productStyle = ProductStyle::where('id', $id)->get()->first();
        $product = Product::where('id', $productStyle->product_id)->get()->first();
        if (!$productStyle) {
            return abort(404);
        }
        $logEvent = [
            Event::consignment()->value
            , Event::csn_order()->value
        ];
        $logPurchase = PurchaseLog::getStockData($logEvent, $depot_id, $id, null);
        $logPurchase = $logPurchase->paginate($data_per_page)->appends($query);
        $title = $product->title. '-'. $productStyle->title;

        return view('cms.commodity.consignment_stock.stock_detail_log', [
            'id' => $id,
            'data_per_page' => $data_per_page,
            'productStyle' => $productStyle,
            'purchaseLog' => $logPurchase,
            'returnAction' => Route('cms.consignment-stock.index', [], true),
            'title' => $title,
            'breadcrumb_data' => $title . ' ' . $productStyle->sku,
        ]);

    }
}

