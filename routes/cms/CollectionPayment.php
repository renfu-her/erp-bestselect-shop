<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\AccountManagement\CollectionPaymentCtrl;


Route::group(['prefix' => 'collection_payment', 'as' => 'collection_payment.'], function () {
    Route::get('', [CollectionPaymentCtrl::class, 'index'])->name('index')->middleware('permission:cms.collection_payment.index');

    Route::match(['get', 'post'], 'edit/{id}', [CollectionPaymentCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.collection_payment.edit');
    Route::match(['get', 'post'], 'edit_note/{id}', [CollectionPaymentCtrl::class, 'edit_note'])->name('edit_note')->middleware('permission:cms.collection_payment.edit');
    Route::get('delete/{id}', [CollectionPaymentCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.collection_payment.delete');

    Route::get('payable_list/{id}', [CollectionPaymentCtrl::class, 'payable_list'])->name('payable_list')->middleware('permission:cms.collection_payment.delete');
    Route::get('payable_delete/{payable_id}', [CollectionPaymentCtrl::class, 'payable_delete'])->name('payable_delete')->middleware('permission:cms.collection_payment.delete');

    Route::match(['get', 'post'], 'claim', [CollectionPaymentCtrl::class, 'claim'])->name('claim')->middleware('permission:cms.collection_payment.index');

    Route::get('po_edit/{id}', [CollectionPaymentCtrl::class, 'po_edit'])->name('po-edit');
    Route::post('po_store/{id}', [CollectionPaymentCtrl::class, 'po_store'])->name('po-store');
    Route::get('po_show/{id}', [CollectionPaymentCtrl::class, 'po_show'])->name('po-show');

    Route::get('refund_po_show/{id}', [CollectionPaymentCtrl::class, 'refund_po_show'])->name('refund-po-show')->middleware('permission:cms.collection_payment.edit');
    Route::get('refund_po_edit/{id}', [CollectionPaymentCtrl::class, 'refund_po_edit'])->name('refund-po-edit');
    Route::post('refund_po_store/{id}', [CollectionPaymentCtrl::class, 'refund_po_store'])->name('refund-po-store');
});
