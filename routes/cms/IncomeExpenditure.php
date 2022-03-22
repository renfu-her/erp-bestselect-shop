<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\Accounting\IncomeExpenditureCtrl;

/**
 * 付款單科目
 */
Route::group(['prefix' => 'income_expenditure', 'as' => 'income_expenditure.'], function () {
    Route::get('', [IncomeExpenditureCtrl::class, 'index'])->name('index')->middleware('permission:cms.income_expenditure.index');
//    Route::get('create', [IncomeExpenditureCtrl::class, 'create'])->name('create')->middleware('permission:cms.income_expenditure.create');
//    Route::post('create', [IncomeExpenditureCtrl::class, 'store']);
    Route::get('edit', [IncomeExpenditureCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.income_expenditure.edit');
    Route::post('edit', [IncomeExpenditureCtrl::class, 'update'])->name('update')->middleware('permission:cms.income_expenditure.update');;
//    Route::get('delete/{id}', [IncomeExpenditureCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.income_expenditure.delete');
});
