<?php

use App\Http\Controllers\Api\Cms\Commodity\DepotCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'depot', 'as' => 'depot.'], function () {
    Route::post('get-select-product', [DepotCtrl::class, 'get_select_product'])->name('get-select-product');// Route('api.cms.depot.get-select-product')
    Route::post('get-select-csn-product', [DepotCtrl::class, 'get_select_csn_product'])->name('get-select-csn-product');
    Route::post('get-csn-product', [DepotCtrl::class, 'get_csn_product'])->name('get-csn-product');
    Route::get('get-pickup-depot', [DepotCtrl::class, 'get_pickup_depot'])->name('get-pickup-depot');
});
