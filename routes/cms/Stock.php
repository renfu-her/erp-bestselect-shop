<?php

use App\Http\Controllers\Cms\Commodity\stockCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'stock', 'as' => 'stock.'], function () {
    Route::get('', [stockCtrl::class, 'index'])->name('index'); //->middleware('permission:cms.stock.index');
});
