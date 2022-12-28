<?php

use App\Http\Controllers\Cms\Marketing\OnePageCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'onepage', 'as' => 'onepage.'], function () {
    Route::get('', [OnePageCtrl::class, 'index'])->name('index')->middleware('permission:cms.onepage.index');
    Route::get('edit/{id}', [OnePageCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.onepage.edit');
    Route::post('edit/{id}', [OnePageCtrl::class, 'update']);
    Route::get('create', [OnePageCtrl::class, 'create'])->name('create')->middleware('permission:cms.onepage.edit');
    Route::post('create', [OnePageCtrl::class, 'store']);
    Route::get('delete/{id}', [OnePageCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.onepage.edit');
    Route::post('active/{id}', [OnePageCtrl::class, 'active'])->name('active')->middleware('permission:cms.onepage.edit');

});
