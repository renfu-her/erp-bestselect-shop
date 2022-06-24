<?php

use App\Http\Controllers\Cms\User\CustomerProfitCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'customer-profit', 'as' => 'customer-profit.'], function () {
    Route::get('', [CustomerProfitCtrl::class, 'index'])->name('index')->middleware('permission:cms.customer-profit.index');
    Route::get('edit/{id}', [CustomerProfitCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.customer-profit.edit');
    Route::post('edit/{id}', [CustomerProfitCtrl::class, 'update']);
    // Route::get('create', [SpecCtrl::class, 'create'])->name('create')->middleware('permission:cms.spec.create');
    // Route::post('create', [SpecCtrl::class, 'store']);
    //    Route::get('delete/{id}', [SpecCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.spec.delete');
});
