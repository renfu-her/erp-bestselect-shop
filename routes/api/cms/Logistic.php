<?php

use App\Http\Controllers\Api\Cms\Commodity\LogisticCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'logistic', 'as' => 'logistic.'], function () {
    Route::post('store-consum', [LogisticCtrl::class, 'store'])->name('create-logistic-consum');
});
