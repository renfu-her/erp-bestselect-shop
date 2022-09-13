<?php

use App\Http\Controllers\Cms\Settings\CreditManagerCtrl;
use Illuminate\Support\Facades\Route;

/**
 * 請款比例
 */
Route::group(['prefix' => 'credit_manager', 'as' => 'credit_manager.'], function () {
    Route::get('', [CreditManagerCtrl::class, 'index'])->name('index')->middleware('permission:cms.credit_manager.index');

    Route::match(['get', 'post'], 'record/{id}', [CreditManagerCtrl::class, 'record'])->name('record')->middleware('permission:cms.credit_manager.index');
    Route::match(['get', 'post'], 'record/edit/{id}', [CreditManagerCtrl::class, 'record_edit'])->name('record-edit')->middleware('permission:cms.credit_manager.index');
    Route::match(['get', 'post'], 'ask', [CreditManagerCtrl::class, 'ask'])->name('ask')->middleware('permission:cms.credit_manager.index');
    Route::match(['get', 'post'], 'claim', [CreditManagerCtrl::class, 'claim'])->name('claim')->middleware('permission:cms.credit_manager.index');
    Route::get('income/{id}', [CreditManagerCtrl::class, 'income_detail'])->name('income-detail')->middleware('permission:cms.credit_manager.index');
});
