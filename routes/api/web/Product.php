<?php

use App\Http\Controllers\Api\Web\ProductCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'product', 'as' => 'product.'], function () {
    Route::get('get/{sku}', [ProductCtrl::class, 'getSingleProduct']);
    Route::post('list', [ProductCtrl::class, 'getCollectionList']);
});


