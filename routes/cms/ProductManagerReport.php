<?php

use App\Http\Controllers\Cms\Commodity\ProductManagerReportCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'product-manager-report', 'as' => 'product-manager-report.'], function () {
    Route::get('', [ProductManagerReportCtrl::class, 'index'])->name('index')->middleware('permission:cms.product-manager-report.index');
    Route::get('product/{user_id}', [ProductManagerReportCtrl::class, 'product'])->name('product')->middleware('permission:cms.product-manager-report.index');

});
