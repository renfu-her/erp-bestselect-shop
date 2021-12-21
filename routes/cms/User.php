<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\UserCtrl;

Route::group(['prefix' => 'user', 'as' => 'user.'], function () {
    Route::get('', [UserCtrl::class, 'index'])->name('index')->middleware('permission:cms.user.index');
    Route::get('create', [UserCtrl::class, 'create'])->name('create')->middleware('permission:cms.user.create');
    Route::post('create', [UserCtrl::class, 'store']);
    Route::get('edit/{id}', [UserCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.user.edit');
    Route::post('edit/{id}', [UserCtrl::class, 'update']);
    Route::get('delete/{id}', [UserCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.user.delete');
});
