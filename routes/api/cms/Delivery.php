<?php

use App\Http\Controllers\Api\Cms\Commodity\DeliveryCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'delivery', 'as' => 'delivery.'], function () {
    Route::get('get-select-inbound-list/{productStyleId}', [DeliveryCtrl::class, 'getSelectInboundList'])->name('get-select-inbound');
    Route::post('store-receive-depot/{deliveryId}/{itemId}/{productStyleId?}', [DeliveryCtrl::class, 'store']);
});
