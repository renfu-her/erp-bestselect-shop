<?php

use App\Http\Controllers\Cms\Settings\CreditBankCtrl;
use Illuminate\Support\Facades\Route;

/**
 * 請款銀行
 */
Route::group(['prefix' => 'credit_bank', 'as' => 'credit_bank.'], function () {
    Route::get('', [CreditBankCtrl::class, 'index'])->name('index')->middleware('permission:cms.credit_bank.index');
    Route::get('create', [CreditBankCtrl::class, 'create'])->name('create')->middleware('permission:cms.credit_bank.create');
    Route::post('create', [CreditBankCtrl::class, 'store']);
     Route::get('edit/{id}', [CreditBankCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.credit_bank.edit');
     Route::post('edit/{id}', [CreditBankCtrl::class, 'update']);
     Route::get('delete/{id}', [CreditBankCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.credit_bank.delete');
});
