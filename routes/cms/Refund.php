<?php

use App\Http\Controllers\Cms\AccountManagement\RefundCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'refund', 'as' => 'refund.'], function () {
    Route::get('', [RefundCtrl::class, 'index'])->name('index')->middleware('permission:cms.refund.index');
});
