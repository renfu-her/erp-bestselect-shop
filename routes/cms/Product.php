<?php

use App\Http\Controllers\Cms\ProductCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'product','as'=>'product.'], function () {
    Route::get('', [ProductCtrl::class, 'index'])->name('index')->middleware('permission:cms.product.index');
    Route::get('edit/{id}', [ProductCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.product.edit');
    Route::post('edit/{id}', [ProductCtrl::class, 'update']);
    Route::get('edit/{id}/style', [ProductCtrl::class, 'editStyle'])->name('edit-style');//->middleware('permission:cms.product.style');
    Route::get('edit/{id}/sale', [ProductCtrl::class, 'editSale'])->name('edit-sale');//->middleware('permission:cms.product.sale');
    Route::get('edit/{id}/web-desc', [ProductCtrl::class, 'editWebDesc'])->name('edit-web-desc');//->middleware('permission:cms.product.web-desc');
    Route::get('edit/{id}/web-spec', [ProductCtrl::class, 'editWebSpec'])->name('edit-web-spec');//->middleware('permission:cms.product.web-spec');
    Route::get('edit/{id}/web-logis', [ProductCtrl::class, 'editWebLogis'])->name('edit-web-logis');//->middleware('permission:cms.product.web-logis');
    Route::get('edit/{id}/setting', [ProductCtrl::class, 'editSetting'])->name('edit-setting');//->middleware('permission:cms.product.setting');

    Route::get('create', [ProductCtrl::class, 'create'])->name('create')->middleware('permission:cms.product.create');
    Route::post('create', [ProductCtrl::class, 'store']);
    Route::get('delete/{id}', [ProductCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.product.delete');
});
