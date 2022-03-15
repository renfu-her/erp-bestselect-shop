<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\Marketing\DiscountCtrl;

Route::group(['prefix' => 'discount', 'as' => 'discount.'], function () {
    Route::get('', [DiscountCtrl::class, 'index'])->name('index')->middleware('permission:cms.discount.index');
    Route::get('create', [DiscountCtrl::class, 'create'])->name('create')->middleware('permission:cms.discount.create');
    Route::post('create', [DiscountCtrl::class, 'store']);
    Route::get('edit/{id}', [DiscountCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.discount.edit');
    Route::post('edit/{id}', [DiscountCtrl::class, 'update']);
    Route::get('delete/{id}', [DiscountCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.discount.delete');
});
