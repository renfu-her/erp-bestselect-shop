<?php

use App\Http\Controllers\Api\Web\BankCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'bank', 'as' => 'bank.'], function () {
    Route::get('list', [BankCtrl::class, 'bankList'])->name('list'); // Route('api.cms.depot.get-select-product')
});
