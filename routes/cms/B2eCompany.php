<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\Settings\B2eCompanyCtrl;

Route::group(['prefix' => 'b2e-company', 'as' => 'b2e-company.'], function () {
    Route::get('', [B2eCompanyCtrl::class, 'index'])->name('index')->middleware('permission:cms.b2e-company.index');
    Route::get('create', [B2eCompanyCtrl::class, 'create'])->name('create')->middleware('permission:cms.b2e-company.create');
    Route::post('create', [B2eCompanyCtrl::class, 'store']);
    Route::get('edit/{id}', [B2eCompanyCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.b2e-company.edit');
    Route::post('edit/{id}', [B2eCompanyCtrl::class, 'update']);
    Route::get('delete/{id}', [B2eCompanyCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.b2e-company.delete');
});
