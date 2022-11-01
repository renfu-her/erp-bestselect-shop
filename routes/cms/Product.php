<?php

use App\Http\Controllers\Cms\Commodity\ProductCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'product', 'as' => 'product.'], function () {
    Route::get('', [ProductCtrl::class, 'index'])->name('index')->middleware('permission:cms.product.index');
    Route::get('edit/{id}', [ProductCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.product.edit');
    Route::post('edit/{id}', [ProductCtrl::class, 'update']);

    // 規格款式
    Route::get('edit/{id}/style', [ProductCtrl::class, 'editStyle'])->name('edit-style')->middleware('permission:cms.product.edit-style');
    Route::post('edit/{id}/style', [ProductCtrl::class, 'storeStyle']); //->middleware('permission:cms.product.style');
    Route::get('edit/{id}/create-sku', [ProductCtrl::class, 'createAllSku'])->name('create-sku');; //->middleware('permission:cms.product.style');

    Route::get('edit/{id}/spec', [ProductCtrl::class, 'editSpec'])->name('edit-spec'); //->middleware('permission:cms.product.style');
    Route::post('edit/{id}/spec', [ProductCtrl::class, 'storeSpec']); //->middleware('permission:cms.product.style');
     // 規格款式
    Route::get('edit/{id}/combo', [ProductCtrl::class, 'editCombo'])->name('edit-combo')->middleware('permission:cms.product.edit-combo');
    Route::post('edit/{id}/combo', [ProductCtrl::class, 'updateCombo']); //->middleware('permission:cms.product.style');
    Route::get('edit/{id}/combo-prod', [ProductCtrl::class, 'createComboProd'])->name('create-combo-prod'); //->middleware('permission:cms.product.style');
    Route::post('edit/{id}/combo-prod', [ProductCtrl::class, 'storeComboProd']); //->middleware('permission:cms.product.style');

    Route::get('edit/{id}/combo-prod/{sid}/edit', [ProductCtrl::class, 'editComboProd'])->name('edit-combo-prod'); //->middleware('permission:cms.product.style');
    Route::post('edit/{id}/combo-prod/{sid}/edit', [ProductCtrl::class, 'updateComboProd']); //->middleware('permission:cms.product.style');

    Route::get('edit/{id}/sale', [ProductCtrl::class, 'editSale'])->name('edit-sale'); //->middleware('permission:cms.product.sale');
    // 庫存管理
    Route::get('edit/{id}/sale/{sid}/stock', [ProductCtrl::class, 'editStock'])->name('edit-stock')->middleware('permission:cms.product.edit-stock');
    Route::post('edit/{id}/sale/{sid}/stock', [ProductCtrl::class, 'updateStock']);
    // 價格管理
    Route::get('edit/{id}/sale/{sid}/price', [ProductCtrl::class, 'editPrice'])->name('edit-price')->middleware('permission:cms.product.edit-price');
    Route::post('edit/{id}/sale/{sid}/price', [ProductCtrl::class, 'updatePrice']);
    // [網頁]商品介紹
    Route::get('edit/{id}/web-desc', [ProductCtrl::class, 'editWebDesc'])->name('edit-web-desc')->middleware('permission:cms.product.edit-web-desc');
    Route::post('edit/{id}/web-desc', [ProductCtrl::class, 'updateWebDesc']); //->middleware('permission:cms.product.web-desc');

    // Route::get('edit/{id}/web-logis', [ProductCtrl::class, 'editWebLogis'])->name('edit-web-logis'); //->middleware('permission:cms.product.web-logis');
    // Route::post('edit/{id}/web-logis', [ProductCtrl::class, 'updateWebLogis']); //->middleware('permission:cms.product.web-logis');
    // [網頁]規格說明
    Route::get('edit/{id}/web-spec', [ProductCtrl::class, 'editWebSpec'])->name('edit-web-spec')->middleware('permission:cms.product.edit-web-spec');
    Route::post('edit/{id}/web-spec', [ProductCtrl::class, 'updateWebSpec']); //->middleware('permission:cms.product.web-spec');
    // 設定
    Route::get('edit/{id}/setting', [ProductCtrl::class, 'editSetting'])->name('edit-setting')->middleware('permission:cms.product.edit-setting');
    Route::post('edit/{id}/setting', [ProductCtrl::class, 'updateSetting']); //->middleware('permission:cms.product.setting');

    Route::get('create', [ProductCtrl::class, 'create'])->name('create')->middleware('permission:cms.product.create');
    Route::post('create', [ProductCtrl::class, 'store']);
    Route::get('delete/{id}', [ProductCtrl::class, 'destroy'])->name('delete'); //->middleware('permission:cms.product.delete');
    // ERP產品資訊
    Route::get('show/{id}', [ProductCtrl::class, 'show'])->name('show')->middleware('permission:cms.product.show');
    // 複製產品資訊
    Route::post('clone/{id}', [ProductCtrl::class, 'clone'])->name('clone')->middleware('permission:cms.product.clone');

    Route::get('export_excel', [ProductCtrl::class, 'export_excel'])->name('export_excel')->middleware('permission:cms.product.export_excel');
});
