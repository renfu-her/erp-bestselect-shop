<?php

use App\Http\Controllers\Cms\AccountManagement\LedgerCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'ledger', 'as' => 'ledger.'], function () {
    Route::get('', [LedgerCtrl::class, 'index'])->name('index')->middleware('permission:cms.ledger.index');
    Route::get('detail', [LedgerCtrl::class, 'detail'])->name('detail');
});
