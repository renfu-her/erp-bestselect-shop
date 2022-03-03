<?php

use App\Http\Controllers\Api\Cms\Commodity\DeliveryCtrl;

Route::group(['prefix' => 'delivery', 'as' => 'delivery.'], function () {
    Route::get('get-select-inbound-list/{product_style_id}', [DeliveryCtrl::class, 'getSelectInboundList'])->name('getSelectInboundList');
    Route::post('store-receive-depot/{deliveryId}/{itemId}', [DeliveryCtrl::class, 'store']);
});
