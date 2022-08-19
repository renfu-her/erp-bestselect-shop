<?php

use App\Http\Controllers\Cms\Commodity\StockCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'stock', 'as' => 'stock.'], function () {
    Route::get('', [StockCtrl::class, 'index'])->name('index')->middleware('permission:cms.stock.index');
    Route::get('stock_detail_log/{depot_id?}/{id}', [StockCtrl::class, 'historyStockDetailLog'])->name('stock_detail_log');
});
