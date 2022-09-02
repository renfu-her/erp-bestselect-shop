<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\AccountManagement\AccountReceivedCsnOrderCtrl;

Route::group(['prefix' => 'ar_csnorder', 'as' => 'ar_csnorder.'], function () {
    Route::get('create/{id}', [AccountReceivedCsnOrderCtrl::class, 'create'])->name('create')->middleware('permission:cms.ar_csnorder.create');
    // Route::get('show', [AccountReceivedCsnOrderCtrl::class, 'show'])->name('show')->middleware('permission:cms.ar_csnorder.show');
    Route::post('store', [AccountReceivedCsnOrderCtrl::class, 'store'])->name('store');
    Route::get('receipt/{id}', [AccountReceivedCsnOrderCtrl::class, 'receipt'])->name('receipt')->middleware('permission:cms.ar_csnorder.receipt');
    Route::match(['get', 'post'], 'review/{id}', [AccountReceivedCsnOrderCtrl::class, 'review'])->name('review')->middleware('permission:cms.ar_csnorder.review');

    Route::match(['get', 'post'], 'taxation/{id}', [AccountReceivedCsnOrderCtrl::class, 'taxation'])->name('taxation')->middleware('permission:cms.ar_csnorder.taxation');
});
