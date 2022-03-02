<?php

use App\Http\Controllers\Cms\User\UserMntCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'usermnt', 'as' => 'usermnt.'], function () {
    Route::get('edit', [UserMntCtrl::class, 'edit'])->name('edit');
    Route::post('edit', [UserMntCtrl::class, 'update']);
    Route::get('customer-binding', [UserMntCtrl::class, 'customerBinding'])->name('customer-binding');
    Route::post('customer-binding', [UserMntCtrl::class, 'updateCustomerBinding']);
});
