<?php

use App\Http\Controllers\Cms\Commodity\ProductCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'product', 'as' => 'product.'], function () {
    Route::get('', [ProductCtrl::class, 'index'])->name('index'); //->middleware('permission:cms.product.index');
    Route::get('edit/{id}', [ProductCtrl::class, 'edit'])->name('edit'); //->middleware('permission:cms.product.edit');
    Route::post('edit/{id}', [ProductCtrl::class, 'update']);
    Route::get('edit/{id}/style', [ProductCtrl::class, 'editStyle'])->name('edit-style'); //->middleware('permission:cms.product.style');
    Route::post('edit/{id}/style', [ProductCtrl::class, 'storeStyle']); //->middleware('permission:cms.product.style');
    Route::get('edit/{id}/create-sku', [ProductCtrl::class, 'createAllSku'])->name('create-sku');; //->middleware('permission:cms.product.style');

    Route::get('edit/{id}/spec', [ProductCtrl::class, 'editSpec'])->name('edit-spec'); //->middleware('permission:cms.product.style');
    Route::post('edit/{id}/spec', [ProductCtrl::class, 'storeSpec']); //->middleware('permission:cms.product.style');

    Route::get('edit/{id}/combo', [ProductCtrl::class, 'editCombo'])->name('edit-combo'); //->middleware('permission:cms.product.style');
    Route::post('edit/{id}/combo', [ProductCtrl::class, 'updateCombo']); //->middleware('permission:cms.product.style');
    Route::get('edit/{id}/combo-prod', [ProductCtrl::class, 'createComboProd'])->name('create-combo-prod'); //->middleware('permission:cms.product.style');
    Route::post('edit/{id}/combo-prod', [ProductCtrl::class, 'storeComboProd']); //->middleware('permission:cms.product.style');

    Route::get('edit/{id}/combo-prod/{sid}/edit', [ProductCtrl::class, 'editComboProd'])->name('edit-combo-prod'); //->middleware('permission:cms.product.style');
    Route::post('edit/{id}/combo-prod/{sid}/edit', [ProductCtrl::class, 'updateComboProd']); //->middleware('permission:cms.product.style');

    Route::get('edit/{id}/sale', [ProductCtrl::class, 'editSale'])->name('edit-sale'); //->middleware('permission:cms.product.sale');
    Route::get('edit/{id}/sale/{sid}/stock', [ProductCtrl::class, 'editStock'])->name('edit-stock'); //->middleware('permission:cms.product.sale');
    Route::get('edit/{id}/sale/{sid}/price', [ProductCtrl::class, 'editPrice'])->name('edit-price'); //->middleware('permission:cms.product.sale');
    Route::get('edit/{id}/web-desc', [ProductCtrl::class, 'editWebDesc'])->name('edit-web-desc'); //->middleware('permission:cms.product.web-desc');
    Route::get('edit/{id}/web-spec', [ProductCtrl::class, 'editWebSpec'])->name('edit-web-spec'); //->middleware('permission:cms.product.web-spec');
    Route::get('edit/{id}/web-logis', [ProductCtrl::class, 'editWebLogis'])->name('edit-web-logis'); //->middleware('permission:cms.product.web-logis');
    Route::get('edit/{id}/setting', [ProductCtrl::class, 'editSetting'])->name('edit-setting'); //->middleware('permission:cms.product.setting');

    Route::get('create', [ProductCtrl::class, 'create'])->name('create'); //->middleware('permission:cms.product.create');
    Route::post('create', [ProductCtrl::class, 'store']);
    Route::get('delete/{id}', [ProductCtrl::class, 'destroy'])->name('delete'); //->middleware('permission:cms.product.delete');
});
