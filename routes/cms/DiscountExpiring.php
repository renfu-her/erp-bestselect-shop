<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\Marketing\DiscountExpiringCtrl;

Route::group(['prefix' => 'discount-expiring', 'as' => 'discount_expiring.'], function () {
    Route::get('', [DiscountExpiringCtrl::class, 'index'])->name('index')->middleware('permission:cms.discount_expiring.index');
    Route::match(['get', 'post'], 'edit/{id}', [DiscountExpiringCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.discount_expiring.edit');
    Route::post('mail_send', [DiscountExpiringCtrl::class, 'mail_send'])->name('mail_send')->middleware('permission:cms.discount_expiring.index');
});
