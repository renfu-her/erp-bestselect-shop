<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\Accounting\PayableDefaultCtrl;

/**
 * 收款單科目
 */
Route::group(['prefix' => 'payable_default', 'as' => 'payable_default.'], function () {
    Route::get('', [PayableDefaultCtrl::class, 'index'])->name('index')->middleware('permission:cms.payable_default.index');
//    Route::get('create', [PayableDefaultCtrl::class, 'create'])->name('create')->middleware('permission:cms.payable_default.create');
//    Route::post('create', [PayableDefaultCtrl::class, 'store']);
    Route::get('edit', [PayableDefaultCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.payable_default.edit');
    Route::post('edit', [PayableDefaultCtrl::class, 'update'])->name('update')->middleware('permission:cms.payable_default.update');;
//    Route::get('delete/{id}', [PayableDefaultCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.payable_default.delete');
});
