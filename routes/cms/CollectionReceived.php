<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\AccountManagement\CollectionReceivedCtrl;

Route::group(['prefix' => 'collection_received', 'as' => 'collection_received.'], function () {
    Route::get('', [CollectionReceivedCtrl::class, 'index'])->name('index')->middleware('permission:cms.collection_received.index');

    Route::match(['get', 'post'], 'edit/{id}', [CollectionReceivedCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.collection_received.edit');
    Route::get('delete/{id}', [CollectionReceivedCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.collection_received.delete');
});
