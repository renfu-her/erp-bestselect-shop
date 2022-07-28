<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\AccountManagement\AccountPayableCtrl;

/**
 * 付款作業
 */
Route::group(['prefix' => 'ap', 'as' => 'ap.'], function () {
    Route::get('', [AccountPayableCtrl::class, 'index'])->name('index')->middleware('permission:cms.ap.index');
});
