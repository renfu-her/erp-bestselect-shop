<?php

use App\Http\Controllers\Cms\Commodity\OrderBonusCtrl;
use Illuminate\Support\Facades\Route;

/**
 * 請款銀行
 */
Route::group(['prefix' => 'order-bonus', 'as' => 'order-bonus.'], function () {
    Route::get('', [OrderBonusCtrl::class, 'index'])->name('index')->middleware('permission:cms.order-bonus.index');
    Route::get('create', [OrderBonusCtrl::class, 'create'])->name('create')->middleware('permission:cms.order-bonus.create');
    Route::post('create', [OrderBonusCtrl::class, 'store']);
   
    //   Route::get('edit/{id}', [OrderBonusCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.order-bonus.edit');
    //  Route::post('edit/{id}', [OrderBonusCtrl::class, 'update']);
    Route::get('delete/{id}', [OrderBonusCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.order-bonus.delete');
    Route::get('detail/{id}', [OrderBonusCtrl::class, 'detail'])->name('detail')->middleware('permission:cms.order-bonus.detail');

});
