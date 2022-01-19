<?php

use App\Http\Controllers\Cms\Frontend\Homepage\BannerCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'banner', 'as' => 'banner.'], function () {
    Route::get('', [BannerCtrl::class, 'index'])->name('index');
});
