<?php

use App\Http\Controllers\Api\AddrCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'addr', 'as' => 'addr.'], function () {
    Route::get('get-regions/{id?}', [AddrCtrl::class, 'getRegions'])->name('get-regions');
    Route::get('formating/{address?}', [AddrCtrl::class, 'addrFormating'])->name('formating');
    Route::get('check-format/{address?}', [AddrCtrl::class, 'checkFormat']);
});
