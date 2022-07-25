<?php

use App\Http\Controllers\Cms\AccountManagement\StituteOrderCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'stitute', 'as' => 'stitute.'], function () {
    Route::get('', [StituteOrderCtrl::class, 'index'])->name('index')->middleware('permission:cms.stitute.index');
    Route::match(['get', 'post'], 'create', [StituteOrderCtrl::class, 'create'])->name('create')->middleware('permission:cms.stitute.create');
    Route::get('show/{id}', [StituteOrderCtrl::class, 'show'])->name('show')->middleware('permission:cms.stitute.show');

    Route::get('po_edit/{id}', [StituteOrderCtrl::class, 'po_edit'])->name('po-edit');
    Route::post('po_store/{id}', [StituteOrderCtrl::class, 'po_store'])->name('po-store');
    Route::get('po_show/{id}', [StituteOrderCtrl::class, 'po_show'])->name('po-show')->middleware('permission:cms.stitute.po-show');
});
