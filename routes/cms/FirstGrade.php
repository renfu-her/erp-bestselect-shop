<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\Accounting\FirstGradeCtrl;

Route::group(['prefix' => 'first_grade', 'as' => 'first_grade.'], function () {
    Route::get('', [FirstGradeCtrl::class, 'index'])->name('index')->middleware('permission:cms.first_grade.index');
    Route::get('create', [FirstGradeCtrl::class, 'create'])->name('create')->middleware('permission:cms.first_grade.create');
    Route::post('create', [FirstGradeCtrl::class, 'store']);
    Route::get('edit/{id}', [FirstGradeCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.first_grade.edit');
    Route::post('edit/{id}', [FirstGradeCtrl::class, 'update']);
    Route::get('delete/{id}', [FirstGradeCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.first_grade.delete');
});
