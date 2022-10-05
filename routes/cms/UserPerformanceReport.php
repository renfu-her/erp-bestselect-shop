<?php

use App\Http\Controllers\Cms\Commodity\UserPerformanceReportCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'user-performance-report', 'as' => 'user-performance-report.'], function () {
    Route::get('', [UserPerformanceReportCtrl::class, 'index'])->name('index')->middleware('permission:cms.user-performance-report.index');
    Route::get('department/{organize_id}', [UserPerformanceReportCtrl::class, 'department'])->name('department')->middleware('permission:cms.user-performance-report.index');

    /*  Route::get('create', [UserPerformanceReportCtrl::class, 'create'])->name('create')->middleware('permission:cms.role.create');
Route::post('create', [UserPerformanceReportCtrl::class, 'store']);
Route::get('edit/{id}', [UserPerformanceReportCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.role.edit');
Route::post('edit/{id}', [UserPerformanceReportCtrl::class, 'update']);
Route::get('delete/{id}', [UserPerformanceReportCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.role.delete');*/
});
