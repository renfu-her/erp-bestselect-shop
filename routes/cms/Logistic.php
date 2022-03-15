<?php

use App\Http\Controllers\Cms\Commodity\LogisticCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'logistic','as'=>'logistic.'], function () {
    Route::get('', [LogisticCtrl::class, 'index'])->name('index')->middleware('permission:cms.logistic.index');
    Route::get('create/{subOrderId}', [LogisticCtrl::class, 'create'])->name('create')->middleware('permission:cms.logistic.create');
    Route::post('create/{logisticId}', [LogisticCtrl::class, 'store']);
    Route::get('delete/{subOrderId}/{consumId}', [LogisticCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.logistic.delete');
});
