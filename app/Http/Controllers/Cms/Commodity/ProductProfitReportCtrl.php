<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInbound;
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

        $products = PurchaseInbound::productStyleListWithExistInbound([], $searchParam)
            ->orderBy('s.product_id')
            ->orderBy('s.id');

        if ($searchParam['stock_status'] == 'in_stock') {
            $products->where('inbound.total_in_stock_num', '>', 0);
        } else {
            $products->where(function ($query) {
                $query->where('inbound.total_in_stock_num', '=', 0)
                        ->orWhereNull('inbound.total_in_stock_num');
            });
        }
        $products = $products->paginate($searchParam['data_per_page'])
            ->appends($query);

        return view('cms.commodity.product_profit_report.list', [
            'query' => $query,
            'dataList' => $products,
            'searchParam' => $searchParam,
        ]);
    }

    public function exportExcel(Request $request)
    {
    }
}
