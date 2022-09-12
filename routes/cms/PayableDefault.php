<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\Accounting\PayableDefaultCtrl;

Route::group(['prefix' => 'payable_default', 'as' => 'payable_default.'], function () {
    Route::get('', [PayableDefaultCtrl::class, 'index'])->name('index')->middleware('permission:cms.payable_default.index');
    Route::get('edit', [PayableDefaultCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.payable_default.edit');
    Route::post('edit', [PayableDefaultCtrl::class, 'update'])->name('update');
});
