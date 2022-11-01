<?php

use App\Http\Controllers\Cms\Settings\OrganizeCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'organize', 'as' => 'organize.'], function () {
    Route::get('', [OrganizeCtrl::class, 'index'])->name('index')->middleware('permission:cms.organize.index');
    Route::get('edit/{id}', [OrganizeCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.organize.edit');
    Route::post('edit/{id}', [OrganizeCtrl::class, 'update']);
});
