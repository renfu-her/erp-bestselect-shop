<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\AccountManagement\AccountReceivedCsnOrderCtrl;

Route::group(['prefix' => 'ar_csnorder', 'as' => 'ar_csnorder.'], function () {
    Route::get('create/{id}', [AccountReceivedCsnOrderCtrl::class, 'create'])->name('create');
    Route::post('store', [AccountReceivedCsnOrderCtrl::class, 'store'])->name('store');
    Route::get('receipt/{id}', [AccountReceivedCsnOrderCtrl::class, 'receipt'])->name('receipt');
    Route::match(['get', 'post'], 'review/{id}', [AccountReceivedCsnOrderCtrl::class, 'review'])->name('review')->middleware('permission:cms.collection_received.edit');
    Route::match(['get', 'post'], 'taxation/{id}', [AccountReceivedCsnOrderCtrl::class, 'taxation'])->name('taxation')->middleware('permission:cms.collection_received.edit');
});
