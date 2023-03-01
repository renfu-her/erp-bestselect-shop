<?php

use App\Http\Controllers\Cms\Settings\ImgStroageCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'img-storage', 'as' => 'img-storage.'], function () {
    Route::get('', [ImgStroageCtrl::class, 'index'])->name('index')->middleware('permission:cms.img-storage.index');
    Route::get('create', [ImgStroageCtrl::class, 'create'])->name('create')->middleware('permission:cms.img-storage.create');
    Route::post('create', [ImgStroageCtrl::class, 'store']);
    Route::get('edit/{id}', [ImgStroageCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.img-storage.edit');
    Route::post('edit/{id}', [ImgStroageCtrl::class, 'update']);
    Route::get('delete/{id}', [ImgStroageCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.img-storage.delete');


});
