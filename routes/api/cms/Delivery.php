<?php

use App\Http\Controllers\Api\Cms\Commodity\DeliveryCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'delivery', 'as' => 'delivery.'], function () {
    Route::post('get-select-inbound-list', [DeliveryCtrl::class, 'getSelectInboundList'])->name('get-select-inbound');
    Route::post('store-receive-depot', [DeliveryCtrl::class, 'store'])->name('create-receive-depot');
    Route::get('del-receive-depot/{receiveDepotId}', [DeliveryCtrl::class, 'destroy'])->name('del-receive-depot');
});
