<?php

use App\Http\Controllers\Cms\AccountManagement\AccountsPayableCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'accounts_payable', 'as' => 'accounts_payable.'], function () {
    Route::get('', [AccountsPayableCtrl::class, 'index'])->name('index')->middleware('permission:cms.accounts_payable.index');
    Route::match(['get', 'post'], 'claim/{type}/{id}/{key}', [AccountsPayableCtrl::class, 'claim'])->name('claim')->middleware('permission:cms.accounts_payable.claim')->where(['type' => '(g|t)']);

    Route::get('po_edit/{id}', [AccountsPayableCtrl::class, 'po_edit'])->name('po-edit');
    Route::post('po_store/{id}', [AccountsPayableCtrl::class, 'po_store'])->name('po-store');
    Route::get('po_show/{id}', [AccountsPayableCtrl::class, 'po_show'])->name('po-show')->middleware('permission:cms.accounts_payable.po-show');
});
