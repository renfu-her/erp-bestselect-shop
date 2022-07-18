<?php

use App\Http\Controllers\Cms\AccountManagement\AccountReceivedCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'account_received', 'as' => 'account_received.'], function () {
    Route::get('', [AccountReceivedCtrl::class, 'index'])->name('index')->middleware('permission:cms.account_received.index');
    Route::match(['get', 'post'], 'claim/{type}/{id}/{key}', [AccountReceivedCtrl::class, 'claim'])->name('claim')->middleware('permission:cms.account_received.claim')->where(['type' => '(g|t)']);

    Route::get('ro_edit/{id}', [AccountReceivedCtrl::class, 'ro_edit'])->name('ro-edit');
    Route::post('ro_store/{id}', [AccountReceivedCtrl::class, 'ro_store'])->name('ro-store');

    Route::get('ro_receipt/{id}', [AccountReceivedCtrl::class, 'ro_receipt'])->name('ro-receipt')->middleware('permission:cms.account_received.ro-receipt');
    Route::match(['get', 'post'], 'ro_review/{id}', [AccountReceivedCtrl::class, 'ro_review'])->name('ro-review')->middleware('permission:cms.account_received.ro-review');
    Route::match(['get', 'post'], 'ro_taxation/{id}', [AccountReceivedCtrl::class, 'ro_taxation'])->name('ro-taxation')->middleware('permission:cms.collection_received.ro-taxation');
});
