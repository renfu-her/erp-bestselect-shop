<?php

use App\Http\Controllers\Api\Cms\Commodity\ProductCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'product', 'as' => 'product.'], function () {
    Route::post('get-product-styles', [ProductCtrl::class, 'getProductStyles'])->name('get-product-styles');
    Route::post('get-products', [ProductCtrl::class, 'getProductList'])->name('get-products');
    Route::post('search-products', [ProductCtrl::class, 'searchProductInfo'])->name('search-products');
    Route::post('get-product-info', [ProductCtrl::class, 'getProductInfo'])->name('get-product-info');
    Route::post('get-product-shipment', [ProductCtrl::class, 'getProductShipment'])->name('get-products-shipment');
});
