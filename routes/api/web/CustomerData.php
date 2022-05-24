<?php

use App\Http\Controllers\Api\Cms\Commodity\DiscountCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'customer-data', 'as' => 'customer-data.'], function () {
    Route::post('get-coupon', [DiscountCtrl::class, "getCoupons"]);
});
