<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\Accounting\FirstGradeCtrl;

Route::group(['prefix' => 'first_grade', 'as' => 'first_grade.'], function () {
    Route::get('', [FirstGradeCtrl::class, 'index'])->name('index')->middleware('permission:cms.first_grade.index');
    Route::get('create', [FirstGradeCtrl::class, 'create'])->name('create')->middleware('permission:cms.first_grade.create');
    Route::post('create', [FirstGradeCtrl::class, 'store']);
});
