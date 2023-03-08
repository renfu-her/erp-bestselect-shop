<?php

use App\Http\Controllers\Cms\Settings\ImgStroageCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'img-storage', 'as' => 'img-storage.'], function () {
    Route::get('', [ImgStroageCtrl::class, 'index'])->name('index');
    Route::get('create', [ImgStroageCtrl::class, 'create'])->name('create');
    Route::post('create', [ImgStroageCtrl::class, 'store']);
    Route::get('edit/{id}', [ImgStroageCtrl::class, 'edit'])->name('edit');
    Route::post('edit/{id}', [ImgStroageCtrl::class, 'update']);
    Route::get('delete/{id}', [ImgStroageCtrl::class, 'destroy'])->name('delete');


});
