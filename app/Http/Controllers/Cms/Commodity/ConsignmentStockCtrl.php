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
        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;

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

    public function historyStockLog(Request $request, $id) {
        $purchaseData = CsnOrder::getData($id)->first();
        $purchaseLog = PurchaseLog::getData(Event::csn_order()->value, $id)->get();
        if (!$purchaseData) {
            return abort(404);
        }

        return view('cms.commodity.purchase.log', [
            'id' => $id,
            'purchaseData' => $purchaseData,
            'purchaseLog' => $purchaseLog,
            'returnAction' => Route('cms.consignment.index', [], true),
            'title' => '寄倉訂購單',
            'sn' => $purchaseData->sn,
            'breadcrumb_data' => $purchaseData->sn,
        ]);
    }

    public function historyStockDetailLog(Request $request, $id) {
        $query = $request->query();
        $data_per_page = Arr::get($query, 'data_per_page', 10);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 10;

        $productStyle = ProductStyle::where('id', $id)->get()->first();
        $product = Product::where('id', $productStyle->product_id)->get()->first();
        if (!$productStyle) {
            return abort(404);
        }
        $logPurchase = PurchaseLog::getCsnStockData($id);
        $logPurchase = $logPurchase->paginate($data_per_page)->appends($query);

        return view('cms.commodity.consignment_stock.stock_detail_log', [
            'id' => $id,
            'data_per_page' => $data_per_page,
            'productStyle' => $productStyle,
            'purchaseLog' => $logPurchase,
            'returnAction' => Route('cms.consignment-stock.stocklist', [], true),
            'title' => $product->title. '-'. $productStyle->title,
            'breadcrumb_data' => $productStyle->sku,
        ]);

    }
}

