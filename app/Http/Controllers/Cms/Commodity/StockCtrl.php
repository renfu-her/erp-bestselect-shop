<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Enums\Delivery\Event;
use App\Exports\Stock\ProductWithExitInboundDetailExport;
use App\Http\Controllers\Controller;
use App\Models\Depot;
use App\Models\Product;
use App\Models\ProductStyle;
use App\Models\PurchaseInbound;
use App\Models\PurchaseLog;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class StockCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $request->query();
        $searchParam = [];
        $searchParam['keyword'] = Arr::get($query, 'keyword');
        $searchParam['type'] = Arr::get($query, 'type');
        $searchParam['consume'] = Arr::get($query, 'consume', '0');
        $searchParam['user'] = Arr::get($query, 'user');
        $searchParam['supplier'] = Arr::get($query, 'supplier');
        $searchParam['stock'] = Arr::get($query, 'stock',[]);
        $searchParam['depot_id'] = Arr::get($query, 'depot_id',[]);
        $searchParam['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page', 100));
      //  dd($searchParam['stock']);
        $typeRadios = [
            'all' => '不限',
            'p' => '一般',
            'c' => '組合包',
        ];
        $consumes = [['all', '不限'], ['1', '耗材'], ['0', '商品']];

        $stockRadios = [
            'warning' => '低於安全庫存',
            'out_of_stock' => '無庫存',
            'still_actual_stock' => '尚有實際庫存',
        ];

        if (!in_array($searchParam['type'], array_keys($typeRadios))) {
            $searchParam['type'] = 'all';
        }
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
            'typeRadios' => $typeRadios,
            'stockRadios' => $stockRadios,
            'consumes' => $consumes,
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
        $logEvent = [
            Event::purchase()->value
            , Event::order()->value
            , Event::ord_pickup()->value
            , Event::consignment()->value
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
            'returnAction' => Route('cms.stock.index', [], true),
            'title' => $title,
            'breadcrumb_data' => $title . ' ' . $productStyle->sku,
        ]);
    }

    public function exportDetail(Request $request)
    {
        $query = $request->input();
        $searchParam = [];
        $searchParam['keyword'] = Arr::get($query, 'keyword');
        $searchParam['type'] = Arr::get($query, 'type');
        $searchParam['consume'] = Arr::get($query, 'consume', '0');
        $searchParam['user'] = Arr::get($query, 'user');
        $searchParam['supplier'] = Arr::get($query, 'supplier');
        $searchParam['stock'] = Arr::get($query, 'stock',[]);
        $searchParam['depot_id'] = Arr::get($query, 'depot_id',[]);
        $searchParam['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page', 100));
        //  dd($searchParam['stock']);
        $typeRadios = [
            'all' => '不限',
            'p' => '一般',
            'c' => '組合包',
        ];

        if (!in_array($searchParam['type'], array_keys($typeRadios))) {
            $searchParam['type'] = 'all';
        }
        $depot_id = $searchParam['depot_id'];
        return (new ProductWithExitInboundDetailExport($depot_id, $searchParam))->download("stock_detail-" . date('YmdHis') . ".xlsx");
    }
}
