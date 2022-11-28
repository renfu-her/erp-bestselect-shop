<?php

use App\Http\Controllers\Cms\Commodity\ProductProfitReportCtrl;
use Illuminate\Support\Facades\Route;

/**
 * 售價利潤報表
 */
Route::group(['prefix' => 'product-profit-report', 'as' => 'product-profit-report.'], function () {
    Route::get('', [ProductProfitReportCtrl::class, 'index'])->name('index')->middleware('permission:cms.product-profit-report.index');
    Route::get('export-excel', [ProductProfitReportCtrl::class, 'exportExcel'])->name('export-excel')->middleware('permission:cms.product-profit-report.export-excel');
});
