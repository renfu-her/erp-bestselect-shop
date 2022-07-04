<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\CustomerCtrl;

Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {
    Route::get('', [CustomerCtrl::class, 'index'])->name('index')->middleware('permission:cms.customer.index');
    Route::get('create', [CustomerCtrl::class, 'create'])->name('create')->middleware('permission:cms.customer.create');
    Route::post('create', [CustomerCtrl::class, 'store']);
    Route::get('edit/{id}', [CustomerCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.customer.edit');
    Route::post('edit/{id}', [CustomerCtrl::class, 'update']);
    Route::get('edit/{id}/coupon', [CustomerCtrl::class, 'coupon'])->name('coupon')->middleware('permission:cms.customer.coupon');
    Route::get('edit/{id}/address', [CustomerCtrl::class, 'address'])->name('address')->middleware('permission:cms.customer.address');
    Route::get('/{id}/order', [CustomerCtrl::class, 'order'])->name('order')->middleware('permission:cms.customer.order');
    Route::get('/{id}/dividend', [CustomerCtrl::class, 'dividend'])->name('dividend')->middleware('permission:cms.customer.dividend');
    Route::get('delete/{id}', [CustomerCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.customer.delete');
});
