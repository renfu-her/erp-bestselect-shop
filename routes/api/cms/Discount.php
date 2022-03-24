<?php

use App\Http\Controllers\Api\Cms\Commodity\DiscountCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'discount', 'as' => 'discount.'], function () {
    Route::post('check-sn', [DiscountCtrl::class, 'checkSn'])->name('check-sn');
    Route::post('change-active', [DiscountCtrl::class, 'changeActive'])->name('change-active');
});
