<?php

use App\Http\Controllers\Cms\Commodity\DeliveryCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'delivery','as'=>'delivery.'], function () {
    Route::get('', [DeliveryCtrl::class, 'index'])->name('index')->middleware('permission:cms.delivery.index');
    //匯出
    Route::get('export_list', [DeliveryCtrl::class, 'exportList'])->name('export-list')->middleware('permission:cms.delivery.index');

    Route::get('create/{event}/{eventId}', [DeliveryCtrl::class, 'create'])->name('create')->middleware('permission:cms.delivery.edit');
    Route::post('store/{deliveryId}', [DeliveryCtrl::class, 'store'])->name('store')->middleware('permission:cms.delivery.edit');
    Route::get('store_cancle/{deliveryId}', [DeliveryCtrl::class, 'store_cancle'])->name('store_cancle')->middleware('permission:cms.delivery.edit');
    Route::get('delete/{event}/{eventId}/{receiveDepotId}', [DeliveryCtrl::class, 'destroyItem'])->name('delete')->middleware('permission:cms.delivery.edit');

    //缺貨
    Route::get('out_stock/{event}/{eventId}', [DeliveryCtrl::class, 'out_stock'])->name('out_stock')->middleware('permission:cms.delivery.edit');
    Route::get('out_stock_delete/{deliveryId}', [DeliveryCtrl::class, 'out_stock_delete'])->name('out_stock_delete')->middleware('permission:cms.delivery.edit');
    Route::post('out_stock_store/{deliveryId}', [DeliveryCtrl::class, 'out_stock_store'])->name('out_stock_store')->middleware('permission:cms.delivery.edit');
    Route::get('out_stock_edit/{event}/{eventId}', [DeliveryCtrl::class, 'out_stock_edit'])->name('out_stock_edit')->middleware('permission:cms.delivery.edit');
    Route::get('out_stock_detail/{event}/{eventId}', [DeliveryCtrl::class, 'out_stock_detail'])->name('out_stock_detail')->middleware('permission:cms.delivery.edit');
    Route::get('print_out_stock/{event}/{eventId}', [DeliveryCtrl::class, 'print_out_stock'])->name('print_out_stock')->middleware('permission:cms.delivery.edit');

    //退貨
    Route::get('back/{event}/{eventId}', [DeliveryCtrl::class, 'back'])->name('back')->middleware('permission:cms.delivery.edit');
    Route::get('back_delete/{deliveryId}', [DeliveryCtrl::class, 'back_delete'])->name('back_delete')->middleware('permission:cms.delivery.edit');
    Route::post('back_store/{deliveryId}', [DeliveryCtrl::class, 'back_store'])->name('back_store')->middleware('permission:cms.delivery.edit');
    Route::get('back_edit/{event}/{eventId}', [DeliveryCtrl::class, 'back_edit'])->name('back_edit')->middleware('permission:cms.delivery.edit');
    Route::get('back_detail/{event}/{eventId}', [DeliveryCtrl::class, 'back_detail'])->name('back_detail')->middleware('permission:cms.delivery.edit');
    Route::get('print_back/{event}/{eventId}', [DeliveryCtrl::class, 'print_back'])->name('print_back')->middleware('permission:cms.delivery.edit');

    //退貨入庫審核
    Route::get('back_inbound/{event}/{eventId}', [DeliveryCtrl::class, 'back_inbound'])->name('back_inbound')->middleware('permission:cms.delivery.edit');
    Route::post('back_inbound_store/{deliveryId}', [DeliveryCtrl::class, 'back_inbound_store'])->name('back_inbound_store')->middleware('permission:cms.delivery.edit');
    Route::get('back_inbound_delete/{deliveryId}', [DeliveryCtrl::class, 'back_inbound_delete'])->name('back_inbound_delete')->middleware('permission:cms.delivery.edit');

    Route::get('roe_po/{id}/{behavior}', [DeliveryCtrl::class, 'roe_po'])->name('roe-po');
    Route::match(['get', 'post'], 'roe_po_create/{id}/{behavior}', [DeliveryCtrl::class, 'roe_po_create'])->name('roe-po-create');
});
