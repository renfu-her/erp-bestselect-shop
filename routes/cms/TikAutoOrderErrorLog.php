<?php

use App\Http\Controllers\Cms\Commodity\TikAutoOrderErrorLogCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'tik_auto_order_error_log', 'as' => 'tik_auto_order_error_log.'], function () {
    Route::get('', [TikAutoOrderErrorLogCtrl::class, 'index'])->name('index')->middleware('permission:cms.stock.tik_auto_order_error_logs');
});
