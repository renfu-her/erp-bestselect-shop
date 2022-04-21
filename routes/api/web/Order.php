<?php

use App\Http\Controllers\Api\Web\OrderCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'order', 'as' => 'order.'], function () {
    Route::post('global-discount', [OrderCtrl::class, 'getGlobalDiscount']);
    Route::get('payinfo', [OrderCtrl::class, 'payinfo']);

    Route::post('credit_card_checkout', [OrderCtrl::class, 'credit_card_checkout'])->name('credit_card_checkout');
});