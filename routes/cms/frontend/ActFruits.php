<?php

use App\Http\Controllers\Cms\Frontend\ActFruitsCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'act-fruits', 'as' => 'act-fruits.'], function () {
    Route::get('', [ActFruitsCtrl::class, 'index'])->name('index')->middleware('permission:cms.act-fruits.index');
    Route::get('edit/{id}', [ActFruitsCtrl::class, 'edit'])->name('edit');
    Route::post('edit/{id}', [ActFruitsCtrl::class, 'update']);
    Route::get('create', [ActFruitsCtrl::class, 'create'])->name('create');
    Route::post('create', [ActFruitsCtrl::class, 'store']);
    Route::get('delete/{id}', [ActFruitsCtrl::class, 'destroy'])->name('delete');

    Route::get('season', [ActFruitsCtrl::class, 'season'])->name('season');
    Route::post('season', [ActFruitsCtrl::class, 'seasonUpdate']);

});
