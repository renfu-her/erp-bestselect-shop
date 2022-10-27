<?php

use App\Http\Controllers\Cms\Commodity\VolumeOfBusinessPerformanceReportCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'vob-performance-report', 'as' => 'vob-performance-report.'], function () {
    Route::get('', [VolumeOfBusinessPerformanceReportCtrl::class, 'index'])->name('index')->middleware('permission:cms.vob-performance-report.index');
    Route::post('renew', [VolumeOfBusinessPerformanceReportCtrl::class, 'renew'])->name('renew')->middleware('permission:cms.vob-performance-report.renew');
    Route::get('export-excel', [VolumeOfBusinessPerformanceReportCtrl::class, 'exportExcel'])->name('export-excel')->middleware('permission:cms.vob-performance-report.index');

  

});
