<?php

use App\Http\Controllers\Cms\Marketing\EdmCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'edm','as'=>'edm.'], function () {
    Route::get('', [EdmCtrl::class, 'index'])->name('index')->middleware('permission:cms.edm.index');
    Route::get('edit/{id}', [EdmCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.edm.edit');
    Route::post('edit/{id}', [EdmCtrl::class, 'update']);
    Route::get('print/{id}/{type}', [EdmCtrl::class, 'print'])->name('print')->middleware('permission:cms.edm.index');
    Route::get('download/{filename?}', [EdmCtrl::class, 'download'])->name('download');

});
