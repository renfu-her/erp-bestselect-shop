<?php

use App\Http\Controllers\Api\Web\OrderCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'order', 'as' => 'order.'], function () {
    Route::post('global-discount', [OrderCtrl::class, 'getGlobalDiscount']);
    Route::get('payinfo', [OrderCtrl::class, 'payinfo']);

    Route::get('payment/credit_card/{id}/{unique_id}', [OrderCtrl::class, 'payment_credit_card'])->name('payment_credit_card');
    Route::post('credit_card_checkout', [OrderCtrl::class, 'credit_card_checkout'])->name('credit_card_checkout');
});