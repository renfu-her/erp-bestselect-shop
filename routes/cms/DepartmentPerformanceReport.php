<?php

use App\Http\Controllers\Cms\Commodity\DepartmentPerformanceReportCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'department-performance-report', 'as' => 'department-performance-report.'], function () {
    Route::get('', [DepartmentPerformanceReportCtrl::class, 'index'])->name('index')->middleware('permission:cms.department-performance-report.index');
    /*
    Route::get('department/{organize_id}', [UserPerformanceReportCtrl::class, 'department'])->name('department')->middleware('permission:cms.user-performance-report.index');
    Route::get('group/{organize_id}', [UserPerformanceReportCtrl::class, 'group'])->name('group')->middleware('permission:cms.user-performance-report.index');
    Route::get('user/{user_id}', [UserPerformanceReportCtrl::class, 'user'])->name('user')->middleware('permission:cms.user-performance-report.index');
    Route::post('renew', [UserPerformanceReportCtrl::class, 'renew'])->name('renew')->middleware('permission:cms.user-performance-report.renew');
*/

});
