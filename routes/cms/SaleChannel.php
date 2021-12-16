<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\SaleChannelCtrl;

Route::group(['prefix' => 'sale_channel', 'as' => 'sale_channel.'], function () {
    Route::get('', [SaleChannelCtrl::class, 'index'])->name('index');
    Route::get('create', [SaleChannelCtrl::class, 'create'])->name('create');
    Route::post('create', [SaleChannelCtrl::class, 'store']);
    Route::get('edit/{id}', [SaleChannelCtrl::class, 'edit'])->name('edit');
    Route::post('edit/{id}', [SaleChannelCtrl::class, 'update']);
    Route::get('delete/{id}', [SaleChannelCtrl::class, 'destroy'])->name('delete');
});

