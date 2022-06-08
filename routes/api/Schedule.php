<?php

use App\Http\Controllers\Api\Cms\ScheduleCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'schedule', 'as' => 'schedule.'], function () {
    Route::get('check-dividend-expired', [ScheduleCtrl::class, 'checkDividendExpired']);
});
