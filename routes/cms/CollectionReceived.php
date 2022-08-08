<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\AccountManagement\CollectionReceivedCtrl;

Route::group(['prefix' => 'collection_received', 'as' => 'collection_received.'], function () {
    Route::get('', [CollectionReceivedCtrl::class, 'index'])->name('index')->middleware('permission:cms.collection_received.index');
    Route::get('create/{id}', [CollectionReceivedCtrl::class, 'create'])->name('create')->middleware('permission:cms.collection_received.create');
    // Route::get('show', [CollectionReceivedCtrl::class, 'show'])->name('show')->middleware('permission:cms.collection_received.show');
    Route::post('store', [CollectionReceivedCtrl::class, 'store'])->name('store');
    Route::get('receipt/{id}', [CollectionReceivedCtrl::class, 'receipt'])->name('receipt')->middleware('permission:cms.collection_received.receipt');
    Route::match(['get', 'post'], 'review/{id}', [CollectionReceivedCtrl::class, 'review'])->name('review')->middleware('permission:cms.collection_received.review');

    Route::get('print_received', [CollectionReceivedCtrl::class, 'print_received'])->name('print_received')->middleware('permission:cms.collection_received.receipt');

    Route::match(['get', 'post'], 'taxation/{id}', [CollectionReceivedCtrl::class, 'taxation'])->name('taxation')->middleware('permission:cms.collection_received.taxation');

    Route::get('delete/{id}', [CollectionReceivedCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.collection_received.delete');
//    Route::get('edit', [CollectionReceivedCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.collection_received.edit');
//    Route::post('edit', [CollectionReceivedCtrl::class, 'update'])->name('update')->middleware('permission:cms.collection_received.update');;
});
