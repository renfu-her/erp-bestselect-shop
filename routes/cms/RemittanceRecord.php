<?php

use App\Http\Controllers\Cms\AccountManagement\RemittanceRecordCtrl;
use Illuminate\Support\Facades\Route;

/**
 * 匯款紀錄
 */
Route::group(['prefix' => 'remittance_record', 'as' => 'remittance_record.'], function () {
    Route::get('', [RemittanceRecordCtrl::class, 'index'])->name('index')->middleware('permission:cms.remittance_record.index');
    Route::get('detail/{sn}', [RemittanceRecordCtrl::class, 'detail'])->name('detail')->middleware('permission:cms.remittance_record.index');
});
