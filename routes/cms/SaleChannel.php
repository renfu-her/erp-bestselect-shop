<?php

use App\Http\Controllers\Cms\SaleChannelCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'sale_channel', 'as' => 'sale_channel.'], function () {
    Route::get('', [SaleChannelCtrl::class, 'index'])->name('index')->middleware('permission:cms.sale_channel.index');
    Route::get('create', [SaleChannelCtrl::class, 'create'])->name('create')->middleware('permission:cms.sale_channel.create');
    Route::post('create', [SaleChannelCtrl::class, 'store']);
    Route::get('edit/{id}', [SaleChannelCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.sale_channel.edit');
    Route::post('edit/{id}', [SaleChannelCtrl::class, 'update']);
    Route::get('delete/{id}', [SaleChannelCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.sale_channel.delete');
    Route::get('batch-price/{id}', [SaleChannelCtrl::class, 'batchPrice'])->name('batch-price')->middleware('permission:cms.sale_channel.edit');
    Route::post('update_dividend_setting', [SaleChannelCtrl::class, 'updateDividendSetting'])->name('update-dividend-setting')->middleware('permission:cms.sale_channel.edit');

});
