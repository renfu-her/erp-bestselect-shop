<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\Accounting\BalanceSheetCtrl;

Route::group(['prefix' => 'balance_sheet', 'as' => 'balance_sheet.'], function () {
    Route::get('', [BalanceSheetCtrl::class, 'index'])->name('index')->middleware('permission:cms.balance_sheet.index');
    Route::get('create', [BalanceSheetCtrl::class, 'create'])->name('create')->middleware('permission:cms.balance_sheet.create');
    Route::post('create', [BalanceSheetCtrl::class, 'store']);
    Route::get('edit/{id}', [BalanceSheetCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.balance_sheet.edit');
    Route::post('edit/{id}', [BalanceSheetCtrl::class, 'update']);
    Route::get('delete/{id}', [BalanceSheetCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.balance_sheet.delete');
});
