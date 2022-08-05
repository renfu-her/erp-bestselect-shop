<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\AccountManagement\CollectionPaymentCtrl;


Route::group(['prefix' => 'collection_payment', 'as' => 'collection_payment.'], function () {
    Route::get('', [CollectionPaymentCtrl::class, 'index'])->name('index')->middleware('permission:cms.collection_payment.index');
});
