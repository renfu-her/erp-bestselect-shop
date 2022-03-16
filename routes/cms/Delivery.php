<?php

use App\Http\Controllers\Cms\Commodity\DeliveryCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'delivery','as'=>'delivery.'], function () {
    Route::get('', [DeliveryCtrl::class, 'index'])->name('index')->middleware('permission:cms.delivery.index');
    Route::get('create/{subOrderId}', [DeliveryCtrl::class, 'create'])->name('create')->middleware('permission:cms.delivery.create');
    Route::post('create/{deliveryId}', [DeliveryCtrl::class, 'store']);
    Route::get('delete/{event}/{eventId}/{receiveDepotId}', [DeliveryCtrl::class, 'destroyItem'])->name('delete')->middleware('permission:cms.delivery.delete');
});
