<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\GeneralLedger\GeneralLedgerCtrl;

Route::group(['prefix' => 'general_ledger', 'as' => 'general_ledger.'], function () {
    Route::get('', [GeneralLedgerCtrl::class, 'index'])->name('index')->middleware('permission:cms.general_ledger.index');
    Route::get('show/{id}/{type}', [GeneralLedgerCtrl::class, 'show'])->name('show')->middleware('permission:cms.general_ledger.show')->where(['type' => '(1st|2nd|3rd|4th)']);
    Route::get('create', [GeneralLedgerCtrl::class, 'create'])->name('create')->middleware('permission:cms.general_ledger.create');
    Route::post('create/{type}', [GeneralLedgerCtrl::class, 'store'])->name('store')->middleware('permission:cms.general_ledger.create')->where(['type' => '(1st|2nd|3rd|4th)']);

    Route::get('edit/{id}/{type}', [GeneralLedgerCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.general_ledger.edit')->where(['type' => '(1st|2nd|3rd|4th)']);
    Route::post('edit/{id}/{type}', [GeneralLedgerCtrl::class, 'update'])->name('update')->middleware('permission:cms.general_ledger.edit')->where(['type' => '(1st|2nd|3rd|4th)']);

    Route::get('delete/{id}', [GeneralLedgerCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.general_ledger.delete');
});
