<?php

use App\Http\Controllers\Cms\PurchaseCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'purchase', 'as' => 'purchase.'], function () {
    Route::get('', [PurchaseCtrl::class, 'index'])->name('index')->middleware('permission:cms.purchase.index');
    Route::get('create', [PurchaseCtrl::class, 'create'])->name('create')->middleware('permission:cms.purchase.create');
    Route::post('create', [PurchaseCtrl::class, 'store']);
    Route::get('edit/{id}', [PurchaseCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.purchase.edit');
    Route::post('edit/{id}', [PurchaseCtrl::class, 'update']);
    Route::get('print_purchase_order/{id}', [PurchaseCtrl::class, 'printPurchase'])->name('print_order')->middleware('permission:cms.purchase.edit');
    Route::get('delete/{id}', [PurchaseCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.purchase.delete');
    Route::post('close/{id}', [PurchaseCtrl::class, 'close'])->name('close')->middleware('permission:cms.purchase.edit');

    Route::get('inbound/{id}', [PurchaseCtrl::class, 'inbound'])->name('inbound')->middleware('permission:cms.purchase.inbound');
    Route::post('store_inbound/{id}', [PurchaseCtrl::class, 'storeInbound'])->name('store_inbound');
    Route::get('delete_inbound/{id}', [PurchaseCtrl::class, 'deleteInbound'])->name('delete_inbound')->middleware('permission:cms.purchase.inbound');

    Route::get('log/{id}', [PurchaseCtrl::class, 'historyLog'])->name('log')->middleware('permission:cms.purchase.index');

    Route::get('edit/{id}/pay-deposit', [PurchaseCtrl::class, 'payDeposit'])->name('pay-deposit');
    Route::get('pay-order/{id}', [PurchaseCtrl::class, 'payOrder'])->name('view-pay-order');
    Route::post('pay-order/{id}', [PurchaseCtrl::class, 'payOrder'])->name('pay-order');
    Route::match(['get', 'post'], 'po_create', [PurchaseCtrl::class, 'po_create'])->name('po-create');

    // 進貨退出
    Route::get('return_list/{purchase_id?}', [PurchaseCtrl::class, 'return_list'])->name('return_list')->middleware('permission:cms.purchase.edit');
    Route::match(['get', 'post'], 'return_create/{purchase_id}', [PurchaseCtrl::class, 'return_create'])->name('return_create')->middleware('permission:cms.purchase.edit');
    Route::match(['get', 'post'], 'return_edit/{return_id}', [PurchaseCtrl::class, 'return_edit'])->name('return_edit')->middleware('permission:cms.purchase.edit');
    Route::get('return_detail/{return_id}', [PurchaseCtrl::class, 'return_detail'])->name('return_detail')->middleware('permission:cms.purchase.edit');
    Route::get('return_delete/{return_id}', [PurchaseCtrl::class, 'return_delete'])->name('return_delete')->middleware('permission:cms.purchase.edit');
    Route::get('print_return/{return_id}', [PurchaseCtrl::class, 'print_return'])->name('print_return')->middleware('permission:cms.purchase.edit');

    //退出入庫審核
    Route::match(['get', 'post'], 'return_inbound/{return_id}', [PurchaseCtrl::class, 'return_inbound'])->name('return_inbound')->middleware('permission:cms.purchase.edit');
    Route::get('return_inbound_delete/{return_id}', [PurchaseCtrl::class, 'return_inbound_delete'])->name('return_inbound_delete')->middleware('permission:cms.purchase.edit');

    Route::get('ro_edit/{return_id}', [PurchaseCtrl::class, 'ro_edit'])->name('ro-edit');
    Route::post('ro_store/{return_id}', [PurchaseCtrl::class, 'ro_store'])->name('ro-store');
    Route::get('ro_receipt/{return_id}', [PurchaseCtrl::class, 'ro_receipt'])->name('ro-receipt');
    Route::match(['get', 'post'], 'ro_review/{return_id}', [PurchaseCtrl::class, 'ro_review'])->name('ro-review')->middleware('permission:cms.collection_received.edit');
    Route::match(['get', 'post'], 'ro_taxation/{return_id}', [PurchaseCtrl::class, 'ro_taxation'])->name('ro-taxation')->middleware('permission:cms.collection_received.edit');
});
