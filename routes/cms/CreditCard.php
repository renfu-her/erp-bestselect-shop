<?php

use App\Http\Controllers\Cms\Settings\CreditCardCtrl;
use Illuminate\Support\Facades\Route;

/**
 * 信用卡
 */
Route::group(['prefix' => 'credit_card', 'as' => 'credit_card.'], function () {
    Route::get('', [CreditCardCtrl::class, 'index'])->name('index')->middleware('permission:cms.credit_card.index');
    Route::get('create', [CreditCardCtrl::class, 'create'])->name('create')->middleware('permission:cms.credit_card.create');
    Route::post('create', [CreditCardCtrl::class, 'store']);
     Route::get('edit/{id}', [CreditCardCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.credit_card.edit');
     Route::post('edit/{id}', [CreditCardCtrl::class, 'update']);
     Route::get('delete/{id}', [CreditCardCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.credit_card.delete');
});
