<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\Commodity\OrderCtrl;

Route::group(['prefix' => 'order', 'as' => 'order.'], function () {
    Route::get('', [OrderCtrl::class, 'index'])->name('index')->middleware('permission:cms.order.index');
    Route::get('create', [OrderCtrl::class, 'create'])->name('create')->middleware('permission:cms.order.create');
    Route::post('create', [OrderCtrl::class, 'store']);
    Route::get('detail/{id}/{subOrderId?}', [OrderCtrl::class, 'detail'])->name('detail')->middleware('permission:cms.order.detail');
    Route::post('detail/{id}', [OrderCtrl::class, 'update']);
    Route::get('delete/{id}', [OrderCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.order.delete');

    Route::get('inbound/{subOrderId}', [OrderCtrl::class, 'inbound'])->name('inbound')->middleware('permission:cms.order.index');
    Route::post('store_inbound/{id}', [OrderCtrl::class, 'storeInbound'])->name('store_inbound');
    Route::get('delete_inbound/{id}', [OrderCtrl::class, 'deleteInbound'])->name('delete_inbound')->middleware('permission:cms.order.create');

    Route::match(['get', 'post'], 'pay/{id}/{sid}', [OrderCtrl::class, 'pay_order'])->name('pay-order')->middleware('permission:cms.order.pay-order');
});
