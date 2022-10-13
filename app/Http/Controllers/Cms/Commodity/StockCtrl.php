<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Delivery\Event;
use App\Exports\Stock\ProductWithExitInboundCheckExport;
use App\Exports\Stock\ProductWithExitInboundDetailExport;
use App\Http\Controllers\Controller;
use App\Models\Consignment;
use App\Models\CsnOrder;
use App\Models\Depot;
use App\Models\Product;
use App\Models\ProductStyle;
use App\Models\Purchase;
use App\Models\PurchaseInbound;
use App\Models\PurchaseLog;
use App\Models\SubOrders;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class StockCtrl extends Controller
{
    private $typeRadios = [
        'all' => '不限',
        'p' => '一般',
        'c' => '組合包',
    ];
    private $consumes = [['all', '不限'], ['1', '耗材'], ['0', '商品']];

    private $stockRadios = [
        'warning' => '低於安全庫存',
        'out_of_stock' => '無庫存',
        'still_actual_stock' => '尚有實際庫存',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $request->query();
        $searchParam = $this->initQueryParam($query);
        $depot_id = $searchParam['depot_id'];

        $products = PurchaseInbound::productStyleListWithExistInbound($depot_id, $searchParam)
            ->orderBy('s.product_id')
            ->orderBy('s.id')
        ;
        $products = $products->paginate($searchParam['data_per_page'])
            ->appends($query);

        return view('cms.commodity.stock.list', [
            'dataList' => $products,
            'suppliers' => Supplier::select('name', 'id', 'vat_no')->get()->toArray(),
            'depotList' => Depot::all(),
            'users' => User::select('id', 'name')->get()->toArray(),
            'typeRadios' => $this->typeRadios,
            'stockRadios' => $this->stockRadios,
            'consumes' => $this->consumes,
            'searchParam' => $searchParam,
        ]);
    }

    public function historyStockDetailLog(Request $request, $depot_id, $id) {
        $query = $request->query();
        $data_per_page = Arr::get($query, 'data_per_page', 100);
        $data_per_page = is_numeric($data_per_page) ? $data_per_page : 100;

        $productStyle = ProductStyle::where('id', $id)->get()->first();
        $product = Product::where('id', $productStyle->product_id)->get()->first();
        if (!$productStyle) {
            return abort(404);
        }

        $depot_id = $depot_id;
        $style_id = $id;
        $logFeature = null;
        $cond = [];
        $log_purchase = PurchaseLog::getStockDataAndEventSn(app(Purchase::class)->getTable(), [Event::purchase()->value], $depot_id, $style_id, $logFeature, $cond);
        $log_order = PurchaseLog::getStockDataAndEventSn(app(SubOrders::class)->getTable(), [Event::order()->value, Event::ord_pickup()->value], $depot_id, $style_id, $logFeature, $cond);
        $log_consignment = PurchaseLog::getStockDataAndEventSn(app(Consignment::class)->getTable(), [Event::consignment()->value], $depot_id, $style_id, $logFeature, $cond);
        $log_csn_order = PurchaseLog::getStockDataAndEventSn(app(CsnOrder::class)->getTable(), [Event::csn_order()->value], $depot_id, $style_id, $logFeature, $cond);

        $log_purchase->union($log_order);
        $log_purchase->union($log_consignment);
        $log_purchase->union($log_csn_order);

        $log_purchase = $log_purchase->orderByDesc('id');
        $log_purchase = $log_purchase->paginate($data_per_page)->appends($query);
        $title = $product->title. '-'. $productStyle->title;

        return view('cms.commodity.consignment_stock.stock_detail_log', [
            'id' => $id,
            'data_per_page' => $data_per_page,
            'productStyle' => $productStyle,
            'purchaseLog' => $log_purchase,
            'returnAction' => Route('cms.stock.index', [], true),
            'title' => $title,
            'breadcrumb_data' => $title . ' ' . $productStyle->sku,
        ]);
    }

    //匯出庫存明細EXCEL
    public function exportDetail(Request $request)
    {
        $query = $request->input();
        $searchParam = $this->initQueryParam($query);
        $depot_id = $searchParam['depot_id'];
        return (new ProductWithExitInboundDetailExport($depot_id, $searchParam))->download("stock-detail-" . date('YmdHis') . ".xlsx");
    }

    //匯出盤點明細EXCEL
    public function exportCheck(Request $request)
    {
        $query = $request->input();
        $searchParam = $this->initQueryParam($query);
        $depot_id = $searchParam['depot_id'];
        return (new ProductWithExitInboundCheckExport($depot_id, $searchParam))->download("stock-check-" . date('YmdHis') . ".xlsx");
    }

    private function initQueryParam($query) {
        $searchParam = [];
        $searchParam['keyword'] = Arr::get($query, 'keyword');
        $searchParam['type'] = Arr::get($query, 'type');
        $searchParam['consume'] = Arr::get($query, 'consume', '0');
        $searchParam['user'] = Arr::get($query, 'user');
        $searchParam['supplier'] = Arr::get($query, 'supplier');
        $searchParam['stock'] = Arr::get($query, 'stock',[]);
        $searchParam['depot_id'] = Arr::get($query, 'depot_id',[]);
        $searchParam['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page', 100));

        if (!in_array($searchParam['type'], array_keys($this->typeRadios))) {
            $searchParam['type'] = 'all';
        }
        return $searchParam;
    }
}
