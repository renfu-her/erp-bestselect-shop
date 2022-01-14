<?php

use App\Http\Controllers\Cms\PurchaseCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'purchase', 'as' => 'purchase.'], function () {
    Route::get('', [PurchaseCtrl::class, 'index'])->name('index')->middleware('permission:cms.purchase.index');
    Route::get('create', [PurchaseCtrl::class, 'create'])->name('create')->middleware('permission:cms.purchase.create');
    Route::post('create', [PurchaseCtrl::class, 'store']);
    Route::get('edit/{id}', [PurchaseCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.purchase.edit');
    Route::post('edit/{id}', [PurchaseCtrl::class, 'update']);
    Route::get('delete/{id}', [PurchaseCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.purchase.delete');
    Route::post('close/{id}', [PurchaseCtrl::class, 'close'])->name('close')->middleware('permission:cms.purchase.close');

    Route::get('inbound/{id}', [PurchaseCtrl::class, 'inbound'])->name('inbound')->middleware('permission:cms.purchase.inbound');
    Route::post('store_inbound/{id}', [PurchaseCtrl::class, 'storeInbound'])->name('store_inbound');
    Route::get('delete_inbound/{id}', [PurchaseCtrl::class, 'deleteInbound'])->name('delete_inbound')->middleware('permission:cms.purchase.delete_inbound');
});
