<?php

use App\Http\Controllers\Cms\Commodity\ConsignmentStockCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'consignment-stock', 'as' => 'consignment-stock.'], function () {
    Route::get('stocklist', [ConsignmentStockCtrl::class, 'stocklist'])->name('index')->middleware('permission:cms.consignment-stock.index');
    Route::get('stock_detail_log/{depot_id?}/{id}', [ConsignmentStockCtrl::class, 'historyStockDetailLog'])->name('stock_detail_log')->middleware('permission:cms.consignment-stock.index');
});
