<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\Accounting\ReceivedDefaultCtrl;

/**
 * 收款單科目
 */
Route::group(['prefix' => 'received_default', 'as' => 'received_default.'], function () {
    Route::get('', [ReceivedDefaultCtrl::class, 'index'])->name('index')->middleware('permission:cms.received_default.index');
//    Route::get('create', [ReceivedDefaultCtrl::class, 'create'])->name('create')->middleware('permission:cms.received_default.create');
//    Route::post('create', [ReceivedDefaultCtrl::class, 'store']);
    Route::get('edit', [ReceivedDefaultCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.received_default.edit');
    Route::post('edit', [ReceivedDefaultCtrl::class, 'update'])->name('update')->middleware('permission:cms.received_default.update');;
//    Route::get('delete/{id}', [ReceivedDefaultCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.received_default.delete');
});
