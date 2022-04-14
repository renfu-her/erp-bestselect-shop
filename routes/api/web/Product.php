<?php

use App\Http\Controllers\Api\Web\ProductCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'product', 'as' => 'product.'], function () {
    Route::post('get', [ProductCtrl::class, 'getSingleProduct']);
});
