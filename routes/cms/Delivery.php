<?php

use App\Http\Controllers\Cms\Commodity\DeliveryCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'delivery','as'=>'delivery.'], function () {
    Route::get('', [DeliveryCtrl::class, 'index'])->name('index')->middleware('permission:cms.delivery.index');
    Route::get('create/{event}/{eventId}', [DeliveryCtrl::class, 'create'])->name('create')->middleware('permission:cms.delivery.edit');
    Route::post('store/{deliveryId}', [DeliveryCtrl::class, 'store'])->name('store')->middleware('permission:cms.delivery.edit');
    Route::get('delete/{event}/{eventId}/{receiveDepotId}', [DeliveryCtrl::class, 'destroyItem'])->name('delete')->middleware('permission:cms.delivery.delete');

    //退貨
    Route::get('back/{event}/{eventId}', [DeliveryCtrl::class, 'back'])->name('back')->middleware('permission:cms.delivery.edit');
    Route::get('back_delete/{deliveryId}', [DeliveryCtrl::class, 'back_delete'])->name('back_delete')->middleware('permission:cms.delivery.edit');
    Route::post('back_store/{deliveryId}', [DeliveryCtrl::class, 'back_store'])->name('back_store')->middleware('permission:cms.delivery.edit');
    Route::get('back_edit/{event}/{eventId}', [DeliveryCtrl::class, 'back_edit'])->name('back_edit')->middleware('permission:cms.delivery.edit');
    Route::get('back_detail/{event}/{eventId}', [DeliveryCtrl::class, 'back_detail'])->name('back_detail')->middleware('permission:cms.delivery.edit');
    //退貨入庫審核
    Route::get('back_inbound/{event}/{eventId}', [DeliveryCtrl::class, 'back_inbound'])->name('back_inbound')->middleware('permission:cms.delivery.edit');
    Route::post('back_inbound_store/{deliveryId}', [DeliveryCtrl::class, 'back_inbound_store'])->name('back_inbound_store')->middleware('permission:cms.delivery.edit');
    Route::get('back_inbound_delete/{deliveryId}', [DeliveryCtrl::class, 'back_inbound_delete'])->name('back_inbound_delete')->middleware('permission:cms.delivery.edit');


    Route::get('return_pay/{id}', [DeliveryCtrl::class, 'return_pay_order'])->name('return-pay-order');
    Route::match(['get', 'post'], 'return_pay_create/{id}', [DeliveryCtrl::class, 'return_pay_create'])->name('return-pay-create');
});
