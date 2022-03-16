<?php

use App\Http\Controllers\Cms\Commodity\LogisticCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'logistic','as'=>'logistic.'], function () {
    Route::get('create/{subOrderId}', [LogisticCtrl::class, 'create'])->name('create')->middleware('permission:cms.logistic.create');
    Route::post('create', [LogisticCtrl::class, 'store']);
    Route::get('delete/{event}/{eventId}/{consumId}', [LogisticCtrl::class, 'destroyItem'])->name('delete')->middleware('permission:cms.logistic.delete');
});
