<?php

use App\Http\Controllers\Api\Cms\Commodity\DiscountCtrl;
use App\Http\Controllers\Api\Web\DividendCtrl;

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'discount', 'as' => 'discount.'], function () {
    Route::post('check-sn', [DiscountCtrl::class, 'checkSn'])->name('check-sn');
    Route::post('change-active', [DiscountCtrl::class, 'changeActive'])->name('change-active');
    Route::post('get-normal-discount', [DiscountCtrl::class, 'getNormalDiscount'])->name('get-normal-discount');
    Route::post('check-discount-code', [DiscountCtrl::class, 'checkDiscountCode'])->name('check-discount-code');
    Route::post('get-coupons', [DiscountCtrl::class, 'getCoupons'])->name('get-coupons');
    Route::post('dividend/point', [DividendCtrl::class, 'getDividendPoint'])->name('get-dividend-point');
    Route::post('change-coupon-event-active', [DiscountCtrl::class, 'changeCouponEventActive'])->name('change-coupon-event-active');


});
