<?php

use App\Http\Controllers\Api\Cms\Commodity\OrderCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'order', 'as' => 'order.'], function () {
    Route::post('change-auto-dividend', [OrderCtrl::class, 'changeAutoDividend'])->name('change-auto-dividend');
    Route::post('active-dividend', [OrderCtrl::class, 'activeDividend'])->name('active-dividend');
    Route::post('update-profit', [OrderCtrl::class, 'updateProfit'])->name('update-profit');

});
