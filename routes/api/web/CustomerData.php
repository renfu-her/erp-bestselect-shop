<?php

use App\Http\Controllers\Api\Cms\Commodity\DiscountCtrl;
use App\Http\Controllers\Api\CustomerCtrl;
use App\Http\Controllers\Api\Web\DividendCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'customer-data', 'as' => 'customer-data.'], function () {
    Route::post('get-coupon', [DiscountCtrl::class, "getCoupons"]);
    Route::post('get-dividend', [DividendCtrl::class, "getDividend"]);
    Route::post('dividend/point', [DividendCtrl::class, "getDividendPoint"]);
    Route::post('attach-identity', [CustomerCtrl::class, 'attachIdentity']);
    Route::post('create-profit', [CustomerCtrl::class, 'createProfit']);
    Route::post('profit-status', [CustomerCtrl::class, 'profitStatus']);
    Route::post('check-recommender', [CustomerCtrl::class, 'checkRecommender']);

});
