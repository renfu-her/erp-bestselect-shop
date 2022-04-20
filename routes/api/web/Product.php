<?php

use App\Http\Controllers\Api\Web\ProductCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'product', 'as' => 'product.'], function () {
    Route::post('list', [ProductCtrl::class, 'getCollectionList']);
    Route::post('get', [ProductCtrl::class, 'getSingleProduct']);
    Route::post('search-products', [ProductCtrl::class, 'searchProductInfo'])->name('search-products');
});


