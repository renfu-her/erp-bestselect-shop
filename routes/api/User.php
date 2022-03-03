<?php

use App\Http\Controllers\Api\Cms\User\UserCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'user', 'as' => 'user.'], function () {
    Route::get('check-customer-bind/{email?}', [UserCtrl::class, 'checkCustomerBind'])->name('check-customer-bind');
    Route::post('get-customer-salechannels', [UserCtrl::class, 'getCustomerSalechannels'])->name('get-customer-salechannels');
});
