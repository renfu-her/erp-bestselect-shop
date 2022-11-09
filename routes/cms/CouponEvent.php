<?php

use App\Http\Controllers\Cms\Marketing\CouponEventCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'coupon-event','as'=>'coupon-event.'], function () {
    Route::get('', [CouponEventCtrl::class, 'index'])->name('index')->middleware('permission:cms.coupon-event.index');
    Route::get('edit/{id}', [CouponEventCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.coupon-event.edit');
    Route::post('edit/{id}', [CouponEventCtrl::class, 'update']);
    Route::get('create', [CouponEventCtrl::class, 'create'])->name('create')->middleware('permission:cms.coupon-event.create');
    Route::post('create', [CouponEventCtrl::class, 'store']);
    Route::get('delete/{id}', [CouponEventCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.coupon-event.delete');
});
