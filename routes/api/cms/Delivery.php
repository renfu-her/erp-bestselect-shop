<?php

use App\Http\Controllers\Api\Cms\Commodity\DeliveryCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'delivery', 'as' => 'delivery.'], function () {
    Route::get('get-select-inbound-list/{product_style_id}', [DeliveryCtrl::class, 'getSelectInboundList'])->name('get-select-inbound');
});
