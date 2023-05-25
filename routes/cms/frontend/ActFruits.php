<?php

use App\Http\Controllers\Cms\Frontend\ActFruitsCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'act-fruits', 'as' => 'act-fruits.'], function () {
    Route::get('', [ActFruitsCtrl::class, 'index'])->name('index')->middleware('permission:cms.act-fruits.index');
    Route::get('edit/{id}', [ActFruitsCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.act-fruits.edit');
    Route::post('edit/{id}', [ActFruitsCtrl::class, 'update']);
    Route::get('create', [ActFruitsCtrl::class, 'create'])->name('create')->middleware('permission:cms.act-fruits.create');
    Route::post('create', [ActFruitsCtrl::class, 'store']);
    Route::get('delete/{id}', [ActFruitsCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.act-fruits.delete');

    Route::get('season', [ActFruitsCtrl::class, 'season'])->name('season')->middleware('permission:cms.act-fruits.season');
});
