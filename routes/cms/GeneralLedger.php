<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\Accounting\GeneralLedgerCtrl;

Route::group(['prefix' => 'general_ledger', 'as' => 'general_ledger.'], function () {
    Route::get('', [GeneralLedgerCtrl::class, 'index'])->name('index')->middleware('permission:cms.general_ledger.index');
    Route::get('create', [GeneralLedgerCtrl::class, 'create'])->name('create')->middleware('permission:cms.general_ledger.create');
    Route::post('create', [GeneralLedgerCtrl::class, 'store']);
    Route::get('edit/{id}', [GeneralLedgerCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.general_ledger.edit');
    Route::post('edit/{id}', [GeneralLedgerCtrl::class, 'update']);
    Route::get('delete/{id}', [GeneralLedgerCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.general_ledger.delete');
});
