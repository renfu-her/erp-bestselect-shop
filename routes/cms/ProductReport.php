<?php

use App\Http\Controllers\Cms\Marketing\ProductReportCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'product-report', 'as' => 'product-report.'], function () {
    Route::get('', [ProductReportCtrl::class, 'index'])->name('index')->middleware('permission:cms.product-report.index');
});
