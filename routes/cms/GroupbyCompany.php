<?php

use App\Http\Controllers\Cms\Settings\GroupbyCompanyCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'groupby-company', 'as' => 'groupby-company.'], function () {
    Route::get('', [GroupbyCompanyCtrl::class, 'index'])->name('index')->middleware('permission:cms.groupby-company.index');
    Route::get('edit/{id}', [GroupbyCompanyCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.groupby-company.edit');
    Route::post('edit/{id}', [GroupbyCompanyCtrl::class, 'update']);
    Route::get('create', [GroupbyCompanyCtrl::class, 'create'])->name('create')->middleware('permission:cms.groupby-company.create');
    Route::post('create', [GroupbyCompanyCtrl::class, 'store']);
//    Route::get('delete/{id}', [GroupbyCompanyCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.groupby-company.delete');
});
