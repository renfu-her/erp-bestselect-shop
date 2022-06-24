<?php

use App\Http\Controllers\Cms\Settings\CreditManagerCtrl;
use Illuminate\Support\Facades\Route;

/**
 * 請款比例
 */
Route::group(['prefix' => 'credit_manager', 'as' => 'credit_manager.'], function () {
    Route::get('', [CreditManagerCtrl::class, 'index'])->name('index')->middleware('permission:cms.credit_manager.index');
});
