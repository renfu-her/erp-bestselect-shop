<?php

use App\Http\Controllers\Api\Web\HomeCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'home', 'as' => 'home.'], function () {
    Route::post('get-banner-list', [HomeCtrl::class, 'getBannerList'])->name('get-banner-list');
    Route::post('template', [HomeCtrl::class, 'getTemplateList'])->name('get-template-list');
    Route::post('template-list1', [HomeCtrl::class, 'getType1']);
    Route::post('get-recommend-collection-list', [HomeCtrl::class, 'getRecommendCollectionList']);
    Route::post('get-same-category-list', [HomeCtrl::class, 'getSameCategoryList']);
    Route::post('get-prd-category-by-shipment', [HomeCtrl::class, 'getProductCategoryByShipment']);
    Route::post('get-prd-list-by-shipment', [HomeCtrl::class, 'getProductListByShipment']);

});
