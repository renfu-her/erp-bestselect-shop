<?php

use App\Http\Controllers\Cms\Commodity\OrderCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'order', 'as' => 'order.'], function () {
    Route::get('', [OrderCtrl::class, 'index'])->name('index')->middleware('permission:cms.order.index');
    Route::get('create', [OrderCtrl::class, 'create'])->name('create')->middleware('permission:cms.order.create');
    Route::post('create', [OrderCtrl::class, 'store']);
    Route::get('detail/{id}/{subOrderId?}', [OrderCtrl::class, 'detail'])->name('detail')->middleware('permission:cms.order.detail');
    Route::post('detail/{id}', [OrderCtrl::class, 'update']);
    Route::get('delete/{id}', [OrderCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.order.delete');

    Route::get('print_order_sales/{id}/{subOrderId}', [OrderCtrl::class, 'print_order_sales'])->name('print_order_sales')->middleware('permission:cms.order.detail');
    Route::get('print_order_ship/{id}/{subOrderId}', [OrderCtrl::class, 'print_order_ship'])->name('print_order_ship')->middleware('permission:cms.order.detail');

    Route::get('inbound/{subOrderId}', [OrderCtrl::class, 'inbound'])->name('inbound')->middleware('permission:cms.order.index');
    Route::post('store_inbound/{id}', [OrderCtrl::class, 'storeInbound'])->name('store_inbound');
    Route::get('delete_inbound/{id}', [OrderCtrl::class, 'deleteInbound'])->name('delete_inbound')->middleware('permission:cms.order.create');

    Route::match(['get', 'post'], 'logistic_pay/{id}/{sid}', [OrderCtrl::class, 'logistic_pay_order'])->name('logistic-pay-order')->middleware('permission:cms.order.logistic-pay-order');
    Route::get('return_pay/{id}/{sid?}', [OrderCtrl::class, 'return_pay_order'])->name('return-pay-order')->middleware('permission:cms.order.return-pay-order');
    Route::match(['get', 'post'], 'return_pay_create/{id}/{sid?}', [OrderCtrl::class, 'return_pay_create'])->name('return-pay-create')->middleware('permission:cms.order.return-pay-order');

    Route::get('invoice/{id}', [OrderCtrl::class, 'create_invoice'])->name('create-invoice')->middleware('permission:cms.order.create-invoice');
    Route::post('invoice/{id}', [OrderCtrl::class, 'store_invoice'])->name('store-invoice');
    Route::post('ajax-detail', [OrderCtrl::class, '_order_detail'])->name('ajax-detail');
    Route::get('invoice/{id}/show', [OrderCtrl::class, 'show_invoice'])->name('show-invoice');
    Route::get('invoice/{id}/re_send', [OrderCtrl::class, 're_send_invoice'])->name('re-send-invoice');

    Route::get('bonus-gross/{id}', [OrderCtrl::class, 'bonus_gross'])->name('bonus-gross');
    Route::get('personal-bonus/{id}', [OrderCtrl::class, 'personal_bonus'])->name('personal-bonus');

    Route::post('change-bonus-owner/{id}', [OrderCtrl::class, 'change_bonus_owner'])->name('change-bonus-owner');

    Route::get('cancel-order/{id}', [OrderCtrl::class, 'cancel_order'])->name('cancel-order');


});
