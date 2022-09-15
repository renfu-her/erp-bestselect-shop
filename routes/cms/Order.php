<?php

use App\Http\Controllers\Cms\Commodity\OrderCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'order', 'as' => 'order.'], function () {
    Route::get('', [OrderCtrl::class, 'index'])->name('index')->middleware('permission:cms.order.index');
    Route::get('create', [OrderCtrl::class, 'create'])->name('create');
    Route::post('create', [OrderCtrl::class, 'store']);
    // 編輯訂單
    Route::get('edit-item/{id}/', [OrderCtrl::class, 'editItem'])->name('edit-item')->middleware('permission:cms.order.edit-item');
    Route::post('edit-item/{id}/', [OrderCtrl::class, 'updateItem']);
    // 訂單明細
    Route::get('detail/{id}/{subOrderId?}', [OrderCtrl::class, 'detail'])->name('detail')->middleware('permission:cms.order.detail');
    Route::post('detail/{id}', [OrderCtrl::class, 'update']);
    Route::get('delete/{id}', [OrderCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.order.delete');

    Route::get('print_order_sales/{id}/{subOrderId}', [OrderCtrl::class, 'print_order_sales'])->name('print_order_sales')->middleware('permission:cms.order.detail');
    Route::get('print_order_ship/{id}/{subOrderId}', [OrderCtrl::class, 'print_order_ship'])->name('print_order_ship')->middleware('permission:cms.order.detail');

    Route::get('inbound/{subOrderId}', [OrderCtrl::class, 'inbound'])->name('inbound')->middleware('permission:cms.order.index');
    Route::post('store_inbound/{id}', [OrderCtrl::class, 'storeInbound'])->name('store_inbound');
    Route::get('delete_inbound/{id}', [OrderCtrl::class, 'deleteInbound'])->name('delete_inbound')->middleware('permission:cms.order.create');

    Route::get('ro_edit/{id}', [OrderCtrl::class, 'ro_edit'])->name('ro-edit');
    Route::post('ro_store/{id}', [OrderCtrl::class, 'ro_store'])->name('ro-store');
    Route::get('ro_receipt/{id}', [OrderCtrl::class, 'ro_receipt'])->name('ro-receipt');
    Route::match(['get', 'post'], 'ro_review/{id}', [OrderCtrl::class, 'ro_review'])->name('ro-review')->middleware('permission:cms.collection_received.edit');
    Route::match(['get', 'post'], 'ro_taxation/{id}', [OrderCtrl::class, 'ro_taxation'])->name('ro-taxation')->middleware('permission:cms.collection_received.edit');

    Route::get('logistic_pay/{id}/{sid}', [OrderCtrl::class, 'logistic_po'])->name('logistic-po');
    Route::match(['get', 'post'], 'logistic_pay_create/{id}/{sid}', [OrderCtrl::class, 'logistic_po_create'])->name('logistic-po-create');
    Route::get('return_pay/{id}/{sid?}', [OrderCtrl::class, 'return_pay_order'])->name('return-pay-order');
    Route::match(['get', 'post'], 'return_pay_create/{id}/{sid?}', [OrderCtrl::class, 'return_pay_create'])->name('return-pay-create');

    Route::get('invoice/{id}', [OrderCtrl::class, 'create_invoice'])->name('create-invoice')->middleware('permission:cms.order_invoice_manager.index');
    Route::post('invoice/{id}', [OrderCtrl::class, 'store_invoice'])->name('store-invoice');
    Route::post('ajax-detail', [OrderCtrl::class, '_order_detail'])->name('ajax-detail');
    Route::get('invoice/{id}/show', [OrderCtrl::class, 'show_invoice'])->name('show-invoice');
    Route::get('invoice/{id}/re_send', [OrderCtrl::class, 're_send_invoice'])->name('re-send-invoice');
    // 獎金毛利
    Route::get('bonus-gross/{id}', [OrderCtrl::class, 'bonus_gross'])->name('bonus-gross')->middleware('permission:cms.order.bonus-gross');

    Route::get('personal-bonus/{id}', [OrderCtrl::class, 'personal_bonus'])->name('personal-bonus');
    // 更換業務員
    Route::post('change-bonus-owner/{id}', [OrderCtrl::class, 'change_bonus_owner'])->name('change-bonus-owner')->middleware('permission:cms.order.change_bonus_owner');
    // 取消訂單
    Route::get('cancel-order/{id}', [OrderCtrl::class, 'cancel_order'])->name('cancel-order')->middleware('permission:cms.order.cancel_order');
    // 分割訂單
    Route::get('split-order/{id}', [OrderCtrl::class, 'split_order'])->name('split-order')->middleware('permission:cms.order.split_order');
    Route::post('split-order/{id}', [OrderCtrl::class, 'update_split_order']);

    // 訂單紀錄
    Route::get('order_flow/{id}', [OrderCtrl::class, 'order_flow'])->name('order-flow')->middleware('permission:cms.order.detail');

});
