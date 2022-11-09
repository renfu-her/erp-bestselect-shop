<?php

use App\Http\Controllers\Cms\Commodity\DeliveryProductCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'delivery_product','as'=>'delivery_product.'], function () {
    Route::get('', [DeliveryProductCtrl::class, 'index'])->name('index')->middleware('permission:cms.delivery.index');
});
