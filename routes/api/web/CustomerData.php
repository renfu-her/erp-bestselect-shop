<?php

use App\Http\Controllers\Api\Cms\Commodity\DiscountCtrl;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Web\DividendCtrl;
Route::group(['prefix' => 'customer-data', 'as' => 'customer-data.'], function () {
    Route::post('get-coupon', [DiscountCtrl::class, "getCoupons"]);
    Route::post('get-dividend', [DividendCtrl::class, "getDividend"]);


});
