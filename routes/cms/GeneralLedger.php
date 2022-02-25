<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\GeneralLedger\GeneralLedgerCtrl;

Route::group(['prefix' => 'general_ledger', 'as' => 'general_ledger.'], function () {
    Route::get('', [GeneralLedgerCtrl::class, 'index'])->name('index')->middleware('permission:cms.general_ledger.index');

    Route::get('create', [GeneralLedgerCtrl::class, 'create'])->name('create')->middleware('permission:cms.general_ledger.create');

    Route::post('create/1st', [GeneralLedgerCtrl::class, 'store'])->name('store-1st')->middleware('permission:cms.general_ledger.create');
    Route::post('create/2nd', [GeneralLedgerCtrl::class, 'store'])->name('store-2nd')->middleware('permission:cms.general_ledger.create');
    Route::post('create/3rd', [GeneralLedgerCtrl::class, 'store'])->name('store-3rd')->middleware('permission:cms.general_ledger.create');
    Route::post('create/4th', [GeneralLedgerCtrl::class, 'store'])->name('store-4th')->middleware('permission:cms.general_ledger.create');

    Route::get('edit/{id}', [GeneralLedgerCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.general_ledger.edit');
    Route::get('edit/{id}/1st', [GeneralLedgerCtrl::class, 'edit'])->name('edit-1st')->middleware('permission:cms.general_ledger.edit');
    Route::get('edit/{id}/2nd', [GeneralLedgerCtrl::class, 'edit'])->name('edit-2nd')->middleware('permission:cms.general_ledger.edit');
    Route::get('edit/{id}/3rd', [GeneralLedgerCtrl::class, 'edit'])->name('edit-3rd')->middleware('permission:cms.general_ledger.edit');
    Route::get('edit/{id}/4th', [GeneralLedgerCtrl::class, 'edit'])->name('edit-4th')->middleware('permission:cms.general_ledger.edit');

    Route::post('edit/{id}/1st', [GeneralLedgerCtrl::class, 'update'])->name('update-1st')->middleware('permission:cms.general_ledger.update');
    Route::post('edit/{id}/2nd', [GeneralLedgerCtrl::class, 'update'])->name('update-2nd')->middleware('permission:cms.general_ledger.update');
    Route::post('edit/{id}/3rd', [GeneralLedgerCtrl::class, 'update'])->name('update-3rd')->middleware('permission:cms.general_ledger.update');
    Route::post('edit/{id}/4th', [GeneralLedgerCtrl::class, 'update'])->name('update-4th')->middleware('permission:cms.general_ledger.update');

    Route::get('show/{id}/1st', [GeneralLedgerCtrl::class, 'show'])->name('show-1st')->middleware('permission:cms.general_ledger.show');
    Route::get('show/{id}/2nd', [GeneralLedgerCtrl::class, 'show'])->name('show-2nd')->middleware('permission:cms.general_ledger.show');
    Route::get('show/{id}/3rd', [GeneralLedgerCtrl::class, 'show'])->name('show-3rd')->middleware('permission:cms.general_ledger.show');
    Route::get('show/{id}/4th', [GeneralLedgerCtrl::class, 'show'])->name('show-4th')->middleware('permission:cms.general_ledger.show');

    Route::get('delete/{id}', [GeneralLedgerCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.general_ledger.delete');
});
