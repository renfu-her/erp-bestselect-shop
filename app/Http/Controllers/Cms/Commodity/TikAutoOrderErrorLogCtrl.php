<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Http\Controllers\Controller;
use App\Models\TikAutoOrderErrorLog;
use Illuminate\Http\Request;

class TikAutoOrderErrorLogCtrl extends Controller
{
    /**
     * 顯示電子票券訂票錯誤紀錄列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 取得搜尋參數
        $searchParam = [
            'orderSn' => $request->input('orderSn'),
            'data_per_page' => $request->input('data_per_page', 10)
        ];

        // 建立查詢建構器
        $query = TikAutoOrderErrorLog::getDataQuery($searchParam['orderSn']);

        // 分頁處理
        $logs = $query->paginate($searchParam['data_per_page']);

        // 返回視圖
        return view('cms.commodity.stock.tik_auto_order_error_logs', [
            'logs' => $logs,
            'searchParam' => $searchParam
        ]);
    }
}
