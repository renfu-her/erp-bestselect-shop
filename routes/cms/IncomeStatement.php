<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\Accounting\IncomeStatementCtrl;

Route::group(['prefix' => 'income_statement', 'as' => 'income_statement.'], function () {
    Route::get('', [IncomeStatementCtrl::class, 'index'])->name('index')->middleware('permission:cms.income_statement.index');
    Route::get('create', [IncomeStatementCtrl::class, 'create'])->name('create')->middleware('permission:cms.income_statement.create');
    Route::post('create', [IncomeStatementCtrl::class, 'store']);
});
