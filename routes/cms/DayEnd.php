<?php

use App\Http\Controllers\Cms\AccountManagement\DayEndCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'day_end', 'as' => 'day_end.'], function () {
    Route::get('', [DayEndCtrl::class, 'index'])->name('index')->middleware('permission:cms.day_end.index');
    Route::post('/edit', [DayEndCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.day_end.edit');
    Route::get('/show/{id}', [DayEndCtrl::class, 'show'])->name('show')->middleware('permission:cms.day_end.show');
});
