<?php

use App\Http\Controllers\Cms\AccountManagement\DayEndCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'day_end', 'as' => 'day_end.'], function () {
    Route::get('', [DayEndCtrl::class, 'index'])->name('index')->middleware('permission:cms.day_end.index');
    Route::post('edit', [DayEndCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.day_end.edit');
    Route::get('detail/{id}', [DayEndCtrl::class, 'detail'])->name('detail')->middleware('permission:cms.day_end.detail');
    Route::get('balance', [DayEndCtrl::class, 'balance'])->name('balance')->middleware('permission:cms.day_end.balance');
    Route::get('balance_check/{id}/{date}', [DayEndCtrl::class, 'balance_check'])->name('balance_check')->middleware('permission:cms.day_end.balance_check');
    Route::get('show', [DayEndCtrl::class, 'show'])->name('show')->middleware('permission:cms.day_end.show');
});
