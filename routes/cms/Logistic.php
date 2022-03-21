<?php

use App\Http\Controllers\Cms\Commodity\LogisticCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'logistic','as'=>'logistic.'], function () {
    Route::get('create/{subOrderId}', [LogisticCtrl::class, 'create'])->name('create')->middleware('permission:cms.logistic.create');
    Route::post('create', [LogisticCtrl::class, 'store']);
    Route::post('audit-inbound', [LogisticCtrl::class, 'auditInbound']);
    Route::get('delete/{event}/{eventId}/{consumId}', [LogisticCtrl::class, 'destroyItem'])->name('delete')->middleware('permission:cms.logistic.delete');

    //修改配送狀態
    Route::get('change-logistic-status/{event}/{eventId}', [LogisticCtrl::class, 'changeLogisticStatus'])->name('changeLogisticStatus');
    Route::get('update-logistic-status/{event}/{eventId}/{deliveryId}/{statusCode}', [LogisticCtrl::class, 'updateLogisticStatus'])->name('updateLogisticStatus');
});
