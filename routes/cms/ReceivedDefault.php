<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\Accounting\ReceivedDefaultCtrl;

Route::group(['prefix' => 'received_default', 'as' => 'received_default.'], function () {
    Route::get('', [ReceivedDefaultCtrl::class, 'index'])->name('index')->middleware('permission:cms.received_default.index');
    Route::get('edit', [ReceivedDefaultCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.received_default.edit');
    Route::post('edit', [ReceivedDefaultCtrl::class, 'update'])->name('update');
});
