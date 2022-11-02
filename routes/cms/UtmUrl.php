<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\Marketing\UtmUrlCtrl;

//網址分享工具
Route::group(['prefix' => 'utm-url', 'as' => 'utm-url.'], function () {
    //行銷設定目錄下
    Route::get('', [UtmUrlCtrl::class, 'index'])->name('index')->middleware('permission:cms.utm-url.index');
    //
//    Route::get('', [UtmUrlCtrl::class, 'list'])->name('list')->middleware('permission:cms.utm-url.list');
});
