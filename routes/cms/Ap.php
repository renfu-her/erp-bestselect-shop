<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\AccountManagement\AccountPayableCtrl;

/**
 * 付款作業
 */
Route::group(['prefix' => 'ap', 'as' => 'ap.'], function () {
    Route::get('', [AccountPayableCtrl::class, 'index'])->name('index')->middleware('permission:cms.ap.index');
    Route::get('create', [AccountPayableCtrl::class, 'create'])->name('create')->middleware('permission:cms.ap.create');
    Route::post('store', [AccountPayableCtrl::class, 'store'])->name('store')->middleware('permission:cms.ap.store');
    Route::get('edit/{id}', [AccountPayableCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.ap.edit');
    Route::post('edit/{id}', [AccountPayableCtrl::class, 'update'])->name('update')->middleware('permission:cms.ap.update');
//    Route::get('delete/{id}', [AccountPayableCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.ap.delete');
});
