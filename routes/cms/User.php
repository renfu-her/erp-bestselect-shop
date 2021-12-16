<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\UserCtrl;

Route::group(['prefix' => 'user', 'as' => 'user.'], function () {
    Route::get('', [UserCtrl::class, 'index'])->name('index');
    Route::get('create', [UserCtrl::class, 'create'])->name('create');
    Route::post('create', [UserCtrl::class, 'store']);
    Route::get('edit/{id}', [UserCtrl::class, 'edit'])->name('edit');
    Route::post('edit/{id}', [UserCtrl::class, 'update']);
    Route::get('delete/{id}', [UserCtrl::class, 'destroy'])->name('delete');
});
