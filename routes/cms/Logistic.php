<?php

use App\Http\Controllers\Cms\Commodity\LogisticCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'logistic','as'=>'logistic.'], function () {
    Route::get('create/{event}/{eventId}', [LogisticCtrl::class, 'create'])->name('create')->middleware('permission:cms.logistic.create');
    Route::post('store', [LogisticCtrl::class, 'store'])->name('store')->middleware('permission:cms.logistic.create');
    Route::post('store-consum', [LogisticCtrl::class, 'storeConsum'])->name('storeConsum')->middleware('permission:cms.logistic.create');
    Route::post('audit-inbound', [LogisticCtrl::class, 'auditInbound'])->name('auditInbound')->middleware('permission:cms.logistic.create');
    Route::get('delete/{event}/{eventId}/{consumId}', [LogisticCtrl::class, 'destroyItem'])->name('delete')->middleware('permission:cms.logistic.delete');

    //修改配送狀態
    Route::get('change-logistic-status/{event}/{eventId}', [LogisticCtrl::class, 'changeLogisticStatus'])->name('changeLogisticStatus')->middleware('permission:cms.logistic.create');
    Route::post('update-logistic-status/{event}/{eventId}/{deliveryId}', [LogisticCtrl::class, 'updateLogisticStatus'])->name('updateLogisticStatus')->middleware('permission:cms.logistic.create');
    //物流託運單
    Route::post('create-logistic-order', [LogisticCtrl::class, 'createLogisticOrder'])->name('createLogisticOrder')->middleware('permission:cms.logistic.create');
    Route::post('delete-logistic-order', [LogisticCtrl::class, 'deleteLogisticOrder'])->name('deleteLogisticOrder')->middleware('permission:cms.logistic.create');
});
