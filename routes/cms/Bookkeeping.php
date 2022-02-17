<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\BookkeepingCtrl;

Route::group(['prefix' => 'bookkeeping', 'as' => 'bookkeeping.'], function () {
    Route::get('', [BookkeepingCtrl::class, 'index'])->name('index')->middleware('permission:cms.bookkeeping.index');
    Route::get('create', [BookkeepingCtrl::class, 'create'])->name('create')->middleware('permission:cms.bookkeeping.create');
    Route::post('create', [BookkeepingCtrl::class, 'store']);
    Route::get('edit/{id}', [BookkeepingCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.bookkeeping.edit');
    Route::post('edit/{id}', [BookkeepingCtrl::class, 'update']);
    Route::get('delete/{id}', [BookkeepingCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.bookkeeping.delete');
});
