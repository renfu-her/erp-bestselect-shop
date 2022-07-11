<?php

use App\Http\Controllers\Api\Web\OrderCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'order', 'as' => 'order.'], function () {
    //   Route::post('global-discount', [OrderCtrl::class, 'getGlobalDiscount']);
    Route::post('create', [OrderCtrl::class, 'createOrder']);
    Route::post('detail', [OrderCtrl::class, 'orderDetail']);

    Route::post('store_remit', [OrderCtrl::class, 'store_remit'])->name('store_remit');
    Route::get('get_remit/{id}', [OrderCtrl::class, 'get_remit'])->name('get_remit');
    /*
Route::get('payinfo', [OrderCtrl::class, 'payinfo']);

Route::get('payment/credit_card/{id}/{unique_id}', [OrderCtrl::class, 'payment_credit_card'])->name('payment_credit_card');
Route::match(['get', 'post'], 'credit_card_checkout/{id}/{unique_id}', [OrderCtrl::class, 'credit_card_checkout'])->name('credit_card_checkout');
Route::post('credit_card_checkout_api/{id}', [OrderCtrl::class, 'credit_card_checkout_api'])->name('credit_card_checkout_api');

Route::post('detail', [OrderCtrl::class, 'orderDetail']);
 */
});
