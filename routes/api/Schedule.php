<?php

use App\Http\Controllers\Api\Cms\ScheduleCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'schedule', 'as' => 'schedule.'], function () {
    Route::get('check-dividend-expired', [ScheduleCtrl::class, 'checkDividendExpired']);
    Route::get('active-dividend', [ScheduleCtrl::class, 'activeDividend']);
    Route::get('order-report-daily', [ScheduleCtrl::class, 'orderReportDaily']);
    Route::get('order-report-month', [ScheduleCtrl::class, 'orderReportMonth']);

    Route::get('customer-report-daily', [ScheduleCtrl::class, 'customerReportDaily']);
    Route::get('customer-report-month', [ScheduleCtrl::class, 'customerReportMonth']);
    Route::get('user-report-month', [ScheduleCtrl::class, 'userReportMonth']);
    Route::get('facebook-shop', [ScheduleCtrl::class, 'facebookShop']);

});
