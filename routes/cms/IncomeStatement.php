<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\Accounting\IncomeStatementCtrl;

Route::group(['prefix' => 'income_statement', 'as' => 'income_statement.'], function () {
    Route::get('', [IncomeStatementCtrl::class, 'index'])->name('index')->middleware('permission:cms.income_statement.index');
    Route::get('create', [IncomeStatementCtrl::class, 'create'])->name('create')->middleware('permission:cms.income_statement.create');
    Route::post('create', [IncomeStatementCtrl::class, 'store']);
    Route::get('edit/{id}', [IncomeStatementCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.income_statement.edit');
    Route::post('edit/{id}', [IncomeStatementCtrl::class, 'update']);
    Route::get('delete/{id}', [IncomeStatementCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.income_statement.delete');
});
