<?php

use App\Http\Controllers\Api\Cms\Commodity\StockCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'stock', 'as' => 'stock.'], function () {
    Route::post('inboundlist', [StockCtrl::class, 'inboundlist'])->name('inboundlist');
});
