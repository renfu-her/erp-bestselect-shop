<?php

use App\Http\Controllers\Cms\DepotCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'depot','as'=>'depot.'], function () {
    Route::get('', [DepotCtrl::class, 'index'])->name('index')->middleware('permission:cms.depot.index');
    Route::get('edit/{id}', [DepotCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.depot.edit');
    Route::post('edit/{id}', [DepotCtrl::class, 'update']);
    Route::get('create', [DepotCtrl::class, 'create'])->name('create')->middleware('permission:cms.depot.create');
    Route::post('create', [DepotCtrl::class, 'store']);
    Route::get('delete/{id}', [DepotCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.depot.delete');

    Route::get('product/{id}', [DepotCtrl::class, 'product_index'])->name('product-index')->middleware('permission:cms.depot.edit');
    Route::match(['get', 'post'], 'product/{id}/create', [DepotCtrl::class, 'product_create'])->name('product-create')->middleware('permission:cms.depot.edit');
    Route::match(['get', 'post'], 'product/{id}/edit', [DepotCtrl::class, 'product_edit'])->name('product-edit')->middleware('permission:cms.depot.edit');
    Route::get('product/{id}/delete', [DepotCtrl::class, 'product_delete'])->name('product-delete')->middleware('permission:cms.depot.edit');
});
