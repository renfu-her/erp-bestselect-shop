<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Exports\Report\ProductProfitExport;
use App\Http\Controllers\Controller;
use App\Models\ProductProfitReport;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Facades\Excel;

class ProductProfitReportCtrl extends Controller
{
    /**
     * @param  Request  $request
     *
        1.商品名稱
        2.款式
        3.售價
        4.售價利潤%(用參考成本去計算)
        5.經銷價
        6.經銷價利潤%(用參考成本去計算)
        7.庫存
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $query = $request->query();
        $searchParam = $this::initParameter($query);
        $products = ProductProfitReport::getProductProfitData($searchParam);
        $products = $products->paginate($searchParam['data_per_page'])
            ->appends($query);

        return view('cms.commodity.product_profit_report.list', [
            'query' => $query,
            'dataList' => $products,
            'searchParam' => $searchParam,
        ]);
    }

    private static function initParameter($query)
    {
        $searchParam['keyword'] = Arr::get($query, 'keyword');
        $searchParam['type'] = Arr::get($query, 'type');
        $searchParam['consume'] = Arr::get($query, 'consume', '0');
        $searchParam['user'] = Arr::get($query, 'user');
        $searchParam['supplier'] = Arr::get($query, 'supplier');
        //不查詢stock狀態（低於安全庫存、 無庫存 、尚有實際庫存）
        $searchParam['stock'] = Arr::get($query, 'stock', []);
        //有無「理貨倉庫存」
        $searchParam['stock_status'] = Arr::get($query, 'stock_status', 'in_stock');
        $searchParam['depot_id'] = Arr::get($query, 'depot_id',[]);
        $searchParam['price'] = 1;
        $searchParam['data_per_page'] = getPageCount(Arr::get($query, 'data_per_page', 100));

        return $searchParam;
    }

    /**
     * @param  Request  $request
     * export 售價利潤報表, 不分庫存狀態，全部匯出
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportExcel(Request $request)
    {
        $query = $request->query();
        $searchParam = $this::initParameter($query);
        $products = ProductProfitReport::getProductProfitData($searchParam);
        $products = $products->get()->toArray();
        $data = [];
        foreach ($products as $product) {
            if ($product->estimated_cost == 0) {
                $price_profit = 0;
                $dealer_profit = 0;
            } else {
                $price_profit = round(($product->price - $product->estimated_cost) * 100 / $product->estimated_cost);
                $dealer_profit = round(($product->dealer_price - $product->estimated_cost) * 100 / $product->estimated_cost);
            }

            $data[] = [
                $product->product_title,
                $product->sku,
                $product->price,
                $price_profit,
                $product->dealer_price,
                $dealer_profit,
                $product->total_in_stock_num,
            ];
        }

        $column_name = [
            '商品名稱',
            '款式',
            '售價',
            '售價利潤(%)',
            '經銷價',
            '經銷價利潤(%)',
            '理貨倉庫存',
        ];
        $export= new ProductProfitExport([
            $column_name,
            $data,
        ]);

        return Excel::download($export, 'product_profit.xlsx');
    }
}
