<?php

use App\Http\Controllers\Cms\Frontend\CollectionCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'collection', 'as' => 'collection.'], function () {
    Route::get('', [CollectionCtrl::class, 'index'])->name('index')->middleware('permission:cms.collection.index');
    Route::get('edit/{id}', [CollectionCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.collection.edit');
    Route::post('edit/{id}', [CollectionCtrl::class, 'update']);
    Route::get('create', [CollectionCtrl::class, 'create'])->name('create')->middleware('permission:cms.collection.create');
    Route::post('create', [CollectionCtrl::class, 'store']);
    Route::get('delete/{id}', [CollectionCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.collection.delete');
    Route::post('publish/{id}', [CollectionCtrl::class, 'publish'])->name('publish')->middleware('permission:cms.collection.edit');
    Route::post('set-edm/{id}', [CollectionCtrl::class, 'setEdm'])->name('set-etm')->middleware('permission:cms.collection.edit');

    Route::post('set-erp-top', [CollectionCtrl::class, 'setErpTop'])->name('set-erp-top')->middleware('permission:cms.collection.edit');

});
