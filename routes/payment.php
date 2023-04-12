<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Cms\PaymentCtrl;

Route::group(['prefix' => 'payment', 'as' => 'payment.'], function () {
    Route::get('credit_card/{id}/{unique_id}', [PaymentCtrl::class, 'credit_card'])->name('credit-card');
    Route::match(['get', 'post'], 'credit_card_checkout/{id}/{unique_id}', [PaymentCtrl::class, 'credit_card_checkout'])->name('credit-card-checkout');
    Route::post('credit_card_result/{id}', [PaymentCtrl::class, 'credit_card_result'])->name('credit-card-result');

    Route::get('line_pay/{source_type}/{source_id}/{unique_id?}', [PaymentCtrl::class, 'line_pay'])->name('line-pay');
    Route::get('line_pay_confirm/{source_type}/{source_id}/{unique_id?}', [PaymentCtrl::class, 'line_pay_confirm'])->name('line-pay-confirm');
});