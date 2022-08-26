<?php

use App\Http\Controllers\Cms\Frontend\CustomPagesCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'custom-pages', 'as' => 'custom-pages.'], function () {
    Route::get('', [CustomPagesCtrl::class, 'index'])->name('index')->middleware('permission:cms.custom-pages.index');
    Route::get('create', [CustomPagesCtrl::class, 'create'])->name('create')->middleware('permission:cms.custom-pages.create');
    Route::post('create', [CustomPagesCtrl::class, 'store']);
    Route::get('edit/{id}', [CustomPagesCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.custom-pages.edit');
    Route::post('edit/{id}', [CustomPagesCtrl::class, 'update']);
    Route::get('delete/{id}', [CustomPagesCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.custom-pages.delete');
});
