<?php

use App\Http\Controllers\Cms\Commodity\ComboProductCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'combo-product', 'as' => 'combo-product.'], function () {
    Route::get('', [ComboProductCtrl::class, 'index'])->name('index'); //->middleware('permission:cms.product.index');
    Route::get('edit/{id}', [ComboProductCtrl::class, 'edit'])->name('edit'); //->middleware('permission:cms.product.edit');
    Route::post('edit/{id}', [ComboProductCtrl::class, 'update']);
    Route::get('edit/{id}/combo', [ComboProductCtrl::class, 'editCombo'])->name('edit-combo'); //->middleware('permission:cms.product.style');
    Route::post('edit/{id}/style', [ComboProductCtrl::class, 'storeStyle']); //->middleware('permission:cms.product.style');
    Route::get('edit/{id}/create-sku', [ComboProductCtrl::class, 'createAllSku'])->name('create-sku');; //->middleware('permission:cms.product.style');

    Route::get('edit/{id}/combo-prop', [ComboProductCtrl::class, 'editComboProd'])->name('edit-combo-prod'); //->middleware('permission:cms.product.style');
    Route::post('edit/{id}/spec', [ComboProductCtrl::class, 'storeSpec']); //->middleware('permission:cms.product.style');

    Route::get('edit/{id}/sale', [ComboProductCtrl::class, 'editSale'])->name('edit-sale'); //->middleware('permission:cms.product.sale');
    Route::get('edit/{id}/web-desc', [ComboProductCtrl::class, 'editWebDesc'])->name('edit-web-desc'); //->middleware('permission:cms.product.web-desc');
    Route::get('edit/{id}/web-spec', [ComboProductCtrl::class, 'editWebSpec'])->name('edit-web-spec'); //->middleware('permission:cms.product.web-spec');
    Route::get('edit/{id}/web-logis', [ComboProductCtrl::class, 'editWebLogis'])->name('edit-web-logis'); //->middleware('permission:cms.product.web-logis');
    Route::get('edit/{id}/setting', [ComboProductCtrl::class, 'editSetting'])->name('edit-setting'); //->middleware('permission:cms.product.setting');

    Route::get('create', [ComboProductCtrl::class, 'create'])->name('create'); //->middleware('permission:cms.product.create');
    Route::post('create', [ComboProductCtrl::class, 'store']);
    Route::get('delete/{id}', [ComboProductCtrl::class, 'destroy'])->name('delete'); //->middleware('permission:cms.product.delete');
});
