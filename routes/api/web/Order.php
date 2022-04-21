<?php

use App\Http\Controllers\Api\Web\OrderCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'order', 'as' => 'order.'], function () {
    Route::post('global-discount', [OrderCtrl::class, 'getGlobalDiscount']);
    Route::post('create', [OrderCtrl::class, 'createOrder']);
    Route::get('payinfo', [OrderCtrl::class, 'payinfo']);

});
