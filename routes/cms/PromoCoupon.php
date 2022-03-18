<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\Marketing\PromoCtrl;

Route::group(['prefix' => 'promo', 'as' => 'promo.'], function () {
    Route::get('', [PromoCtrl::class, 'index'])->name('index')->middleware('permission:cms.promo.index');
    Route::get('create', [PromoCtrl::class, 'create'])->name('create')->middleware('permission:cms.promo.create');
    Route::post('create', [PromoCtrl::class, 'store']);
    Route::get('edit/{id}', [PromoCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.promo.edit');
    Route::post('edit/{id}', [PromoCtrl::class, 'update']);
    Route::get('delete/{id}', [PromoCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.promo.delete');
});
