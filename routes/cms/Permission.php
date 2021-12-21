<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\PermissionCtrl;

Route::group(['prefix' => 'permission', 'as' => 'permission.', 'middleware' => 'role:Super Admin'], function () {
    Route::get('', [PermissionCtrl::class, 'index'])->name('index')->middleware('permission:cms.permission.index');
    Route::get('edit/{id}', [PermissionCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.permission.edit');
    Route::post('edit/{id}', [PermissionCtrl::class, 'update']);
    Route::get('create', [PermissionCtrl::class, 'create'])->name('create')->middleware('permission:cms.permission.create');
    Route::post('create', [PermissionCtrl::class, 'store']);
    Route::get('delete/{id}', [PermissionCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.permission.delete');

    Route::get('child/{id}', [PermissionCtrl::class, 'child'])->name('child')->middleware('permission:cms.permission.child');
    Route::get('child/{id}/edit/{cid}', [PermissionCtrl::class, 'childEdit'])->name('child-edit');
    Route::post('child/{id}/edit/{cid}', [PermissionCtrl::class, 'childUpdate']);
    Route::get('child/{id}/create', [PermissionCtrl::class, 'childCreate'])->name('child-create');
    Route::post('child/{id}/create', [PermissionCtrl::class, 'childStore']);
    Route::get('child/{id}/delete/{cid}', [PermissionCtrl::class, 'childDestroy'])->name('child-delete');
});
