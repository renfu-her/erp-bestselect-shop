<?php

use App\Http\Controllers\Cms\Frontend\NaviNodeCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'navinode','as'=>'navinode.'], function () {
    Route::get('/{level?}', [NaviNodeCtrl::class, 'index'])->name('index');
  /*  Route::get('edit/{id}', [NaviNodeCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.depot.edit');
    Route::post('edit/{id}', [NaviNodeCtrl::class, 'update']);
    Route::get('create', [NaviNodeCtrl::class, 'create'])->name('create')->middleware('permission:cms.depot.create');
    Route::post('create', [NaviNodeCtrl::class, 'store']);
    Route::get('delete/{id}', [NaviNodeCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.depot.delete');*/
});
