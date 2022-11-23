<?php

use App\Http\Controllers\Cms\Marketing\edmCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'edm','as'=>'edm.'], function () {
    Route::get('', [edmCtrl::class, 'index'])->name('index')->middleware('permission:cms.edm.index');
    Route::get('edit/{id}', [edmCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.edm.edit');
    Route::post('edit/{id}', [edmCtrl::class, 'update']);
    Route::get('print/{id}/{type}', [edmCtrl::class, 'print'])->name('print')->middleware('permission:cms.edm.index');

});
