<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\AccountManagement\AccountReceivedCtrl;

Route::group(['prefix' => 'ar', 'as' => 'ar.'], function () {
    Route::get('', [AccountReceivedCtrl::class, 'index'])->name('index')->middleware('permission:cms.ar.index');
//    Route::get('create', [AccountReceivedCtrl::class, 'create'])->name('create')->middleware('permission:cms.ar.create');
//    Route::post('create', [AccountReceivedCtrl::class, 'store']);
    Route::get('edit', [AccountReceivedCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.ar.edit');
    Route::post('edit', [AccountReceivedCtrl::class, 'update'])->name('update')->middleware('permission:cms.ar.update');;
//    Route::get('delete/{id}', [AccountReceivedCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.ar.delete');
});
