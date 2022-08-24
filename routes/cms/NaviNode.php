<?php

use App\Http\Controllers\Cms\Frontend\NaviNodeCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'navinode', 'as' => 'navinode.'], function () {

    Route::get('', [NaviNodeCtrl::class, 'index'])->name('index')->middleware('permission:cms.navinode.index');
    Route::get('create', [NaviNodeCtrl::class, 'create'])->name('create')->middleware('permission:cms.navinode.create');
    Route::post('create', [NaviNodeCtrl::class, 'store']);
    Route::get('edit/{id}', [NaviNodeCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.navinode.edit');
    Route::post('edit/{id}', [NaviNodeCtrl::class, 'update']);
    Route::post('update-level', [NaviNodeCtrl::class, 'updateLevel'])->name('update-level')->middleware('permission:cms.navinode.edit');

});
