<?php

use App\Http\Controllers\Cms\Settings\CreditPercentCtrl;
use Illuminate\Support\Facades\Route;

/**
 * 請款比例
 */
Route::group(['prefix' => 'credit_percent', 'as' => 'credit_percent.'], function () {
    Route::get('', [CreditPercentCtrl::class, 'index'])->name('index')->middleware('permission:cms.credit_percent.index');
     Route::get('edit/{bank_id}/{credit_id}', [CreditPercentCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.credit_percent.edit');
     Route::post('edit/{bank_id}/{credit_id}', [CreditPercentCtrl::class, 'update']);
});
