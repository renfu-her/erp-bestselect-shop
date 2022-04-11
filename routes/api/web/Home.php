<?php

use App\Http\Controllers\Api\Cms\HomeCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'home', 'as' => 'home.'], function () {
    Route::post('banner', [HomeCtrl::class, 'getBannerList'])->name('get-banner-list');
    Route::post('template', [HomeCtrl::class, 'getTemplateList'])->name('get-template-list');
});
