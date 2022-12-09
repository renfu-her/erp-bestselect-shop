<?php

use App\Http\Controllers\Cms\Commodity\DeliveryProductCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'delivery_product','as'=>'delivery_product.'], function () {
    Route::get('', [DeliveryProductCtrl::class, 'index'])->name('index')->middleware('permission:cms.delivery_product.index');
    //匯出
    Route::get('export_list', [DeliveryProductCtrl::class, 'exportList'])->name('export-list')->middleware('permission:cms.delivery_product.index');
});
