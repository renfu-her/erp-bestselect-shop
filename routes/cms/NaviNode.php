<?php

use App\Http\Controllers\Cms\Frontend\NaviNodeCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'navinode', 'as' => 'navinode.'], function () {
    Route::get('/{level?}', [NaviNodeCtrl::class, 'index'])->name('index');
    Route::get('/{level?}/edit/{id}', [NaviNodeCtrl::class, 'edit'])->name('edit');
    Route::post('/{level?}/edit/{id}', [NaviNodeCtrl::class, 'update']);
    Route::get('/{level?}/create', [NaviNodeCtrl::class, 'create'])->name('create');
    Route::post('/{level?}/create', [NaviNodeCtrl::class, 'store']);
    Route::get('/{level?}/delete/{id}', [NaviNodeCtrl::class, 'destroy'])->name('delete');
    Route::get('/{level?}/sort', [NaviNodeCtrl::class, 'sort'])->name('sort');

});
