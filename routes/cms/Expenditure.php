<?php

use App\Http\Controllers\Cms\AdminManagement\ExpenditureCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'expenditure', 'as' => 'expenditure.'], function () {
    Route::get('', [ExpenditureCtrl::class, 'index'])->name('index')->middleware('permission:cms.expenditure.index');
    Route::get('create', [ExpenditureCtrl::class, 'create'])->name('create')->middleware('permission:cms.expenditure.index');
    Route::post('create', [ExpenditureCtrl::class, 'store']);
    Route::get('edit/{id}', [ExpenditureCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.expenditure.index');
    Route::post('edit/{id}', [ExpenditureCtrl::class, 'update']);
    Route::get('delete/{id}', [ExpenditureCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.expenditure.index');
    Route::get('show/{id}', [ExpenditureCtrl::class, 'show'])->name('show');

    Route::get('audit-list', [ExpenditureCtrl::class, 'auditList'])->name('audit-list');
    Route::get('audit-confirm/{id}', [ExpenditureCtrl::class, 'auditEdit'])->name('audit-confirm');
    Route::post('audit-confirm/{id}', [ExpenditureCtrl::class, 'auditConfirm']);

});
