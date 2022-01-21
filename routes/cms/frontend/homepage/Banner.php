<?php

use App\Http\Controllers\Cms\Frontend\Homepage\BannerCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'banner', 'as' => 'banner.'], function () {
    Route::get('', [BannerCtrl::class, 'index'])->name('index');
    Route::post('sort', [BannerCtrl::class, 'sort'])->name('sort');
    Route::get('edit/{id}', [BannerCtrl::class, 'edit'])->name('edit');
    Route::post('edit/{id}', [BannerCtrl::class, 'update']);
    Route::get('create', [BannerCtrl::class, 'create'])->name('create');
    Route::post('create', [BannerCtrl::class, 'store']);
    Route::get('delete/{id}', [BannerCtrl::class, 'destroy'])->name('delete');
});
