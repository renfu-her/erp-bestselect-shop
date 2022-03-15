<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\Marketing\PromoCouponCtrl;

Route::group(['prefix' => 'promo-coupon', 'as' => 'promo-coupon.'], function () {
    Route::get('', [PromoCouponCtrl::class, 'index'])->name('index')->middleware('permission:cms.promo-coupon.index');
    Route::get('create', [PromoCouponCtrl::class, 'create'])->name('create')->middleware('permission:cms.promo-coupon.create');
    Route::post('create', [PromoCouponCtrl::class, 'store']);
    Route::get('edit/{id}', [PromoCouponCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.promo-coupon.edit');
    Route::post('edit/{id}', [PromoCouponCtrl::class, 'update']);
    Route::get('delete/{id}', [PromoCouponCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.promo-coupon.delete');
});
