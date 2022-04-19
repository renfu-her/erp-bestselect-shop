<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\AccountManagement\AccountReceivedCtrl;

Route::group(['prefix' => 'ar', 'as' => 'ar.'], function () {
    // Route::get('', [AccountReceivedCtrl::class, 'index'])->name('index')->middleware('permission:cms.ar.index');
    Route::get('create/{id}', [AccountReceivedCtrl::class, 'create'])->name('create')->middleware('permission:cms.ar.create');
    // Route::get('show', [AccountReceivedCtrl::class, 'show'])->name('show')->middleware('permission:cms.ar.show');
    Route::post('store', [AccountReceivedCtrl::class, 'store'])->name('store');
    // Route::post('review', [AccountReceivedCtrl::class, 'review'])->name('review')->middleware('permission:cms.ar.review');
//    Route::get('edit', [AccountReceivedCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.ar.edit');
//    Route::post('edit', [AccountReceivedCtrl::class, 'update'])->name('update')->middleware('permission:cms.ar.update');;
//    Route::get('delete/{id}', [AccountReceivedCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.ar.delete');
});
