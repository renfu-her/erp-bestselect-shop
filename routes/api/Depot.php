<?php

use App\Http\Controllers\Api\Cms\Commodity\DepotCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'depot', 'as' => 'depot.'], function () {
    Route::post('get-select-product', [DepotCtrl::class, 'get_select_product'])->name('get-select-product');// Route('api.cms.depot.get-select-product')
});
