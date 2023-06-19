<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\User\DividendCtrl;


Route::group(['prefix' => 'user-dividend', 'as' => 'user-dividend.'], function () {
    Route::get('', [DividendCtrl::class, 'index'])->name('index')->middleware('permission:cms.user-dividend.index');
    Route::get('log/{id}', [DividendCtrl::class, 'log'])->name('log');

    /*Route::get('create', [DiscountCtrl::class, 'create'])->name('create')->middleware('permission:cms.discount.create');
    Route::post('create', [DiscountCtrl::class, 'store']);
    Route::get('edit/{id}', [DiscountCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.discount.edit');
    Route::post('edit/{id}', [DiscountCtrl::class, 'update']);
    Route::get('delete/{id}', [DiscountCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.discount.delete');*/
});
