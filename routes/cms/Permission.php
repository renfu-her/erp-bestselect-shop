<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\PermissionCtrl;

Route::group(['prefix' => 'permission', 'as' => 'permission.', 'middleware' => 'role:Super Admin'], function () {
    Route::get('', [PermissionCtrl::class, 'index'])->name('index');
    Route::get('edit/{id}', [PermissionCtrl::class, 'edit'])->name('edit');
    Route::post('edit/{id}', [PermissionCtrl::class, 'update']);
    Route::get('create', [PermissionCtrl::class, 'create'])->name('create');
    Route::post('create', [PermissionCtrl::class, 'store']);
    Route::get('delete/{id}', [PermissionCtrl::class, 'destroy'])->name('delete');

    Route::get('child/{id}', [PermissionCtrl::class, 'child'])->name('child');
    Route::get('child/{id}/edit/{cid}', [PermissionCtrl::class, 'childEdit'])->name('child-edit');
    Route::post('child/{id}/edit/{cid}', [PermissionCtrl::class, 'childUpdate']);
    Route::get('child/{id}/create', [PermissionCtrl::class, 'childCreate'])->name('child-create');
    Route::post('child/{id}/create', [PermissionCtrl::class, 'childStore']);
    Route::get('child/{id}/delete/{cid}', [PermissionCtrl::class, 'childDestroy'])->name('child-delete');
});
