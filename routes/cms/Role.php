<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\RoleCtrl;

Route::group(['prefix' => 'role', 'as' => 'role.'], function () {
    Route::get('', [RoleCtrl::class, 'index'])->name('index');
    Route::get('create', [RoleCtrl::class, 'create'])->name('create');
    Route::post('create', [RoleCtrl::class, 'store']);
    Route::get('edit/{id}', [RoleCtrl::class, 'edit'])->name('edit');
    Route::post('edit/{id}', [RoleCtrl::class, 'update']);
    Route::get('delete/{id}', [RoleCtrl::class, 'destroy'])->name('delete');
});
