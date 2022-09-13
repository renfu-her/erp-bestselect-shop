<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\AccountManagement\CollectionPaymentCtrl;


Route::group(['prefix' => 'collection_payment', 'as' => 'collection_payment.'], function () {
    Route::get('', [CollectionPaymentCtrl::class, 'index'])->name('index')->middleware('permission:cms.collection_payment.index');

    Route::match(['get', 'post'], 'edit/{id}', [CollectionPaymentCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.collection_payment.edit');
    Route::get('delete/{id}', [CollectionPaymentCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.collection_payment.delete');

    Route::get('payable_list/{id}', [CollectionPaymentCtrl::class, 'payable_list'])->name('payable_list')->middleware('permission:cms.collection_payment.delete');
    Route::get('payable_delete/{payable_id}', [CollectionPaymentCtrl::class, 'payable_delete'])->name('payable_delete')->middleware('permission:cms.collection_payment.delete');
});
