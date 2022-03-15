<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\Marketing\PromoCodeCtrl;

Route::group(['prefix' => 'promo-code', 'as' => 'promo-code.'], function () {
    Route::get('', [PromoCodeCtrl::class, 'index'])->name('index')->middleware('permission:cms.promo-code.index');
    Route::get('create', [PromoCodeCtrl::class, 'create'])->name('create')->middleware('permission:cms.promo-code.create');
    Route::post('create', [PromoCodeCtrl::class, 'store']);
    Route::get('edit/{id}', [PromoCodeCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.promo-code.edit');
    Route::post('edit/{id}', [PromoCodeCtrl::class, 'update']);
    Route::get('delete/{id}', [PromoCodeCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.promo-code.delete');
});
