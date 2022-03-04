<?php

use App\Http\Controllers\Cms\Frontend\NaviNodeCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'navinode', 'as' => 'navinode.'], function () {
    /*
   Route::get('static/{level?}', [NaviNodeCtrl::class, 'index'])->name('index');
   
    Route::post('static/{level?}/edit/{id}', [NaviNodeCtrl::class, 'update']);
    Route::get('static/{level?}/create', [NaviNodeCtrl::class, 'create'])->name('create');
    Route::post('static/{level?}/create', [NaviNodeCtrl::class, 'store']);
    Route::get('static/{level?}/delete/{id}', [NaviNodeCtrl::class, 'destroy'])->name('delete');
    Route::get('static/{level?}/sort', [NaviNodeCtrl::class, 'sort'])->name('sort');

    */
    // 設計版
    Route::get('/edit/{id}', [NaviNodeCtrl::class, 'edit'])->name('edit');
    Route::post('/edit/{id}', [NaviNodeCtrl::class, 'update']);

    Route::get('', [NaviNodeCtrl::class, 'index'])->name('index');
    Route::get('design/create', [NaviNodeCtrl::class, 'create2'])->name('create2');
    Route::post('design/create', [NaviNodeCtrl::class, 'store']);
    Route::post('design/update-level', [NaviNodeCtrl::class, 'updateLevel'])->name('update-level');

});
