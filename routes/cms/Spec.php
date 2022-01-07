<?php

use App\Http\Controllers\Cms\Settings\SpecCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'spec','as'=>'spec.'], function () {
    Route::get('', [SpecCtrl::class, 'index'])->name('index')->middleware('permission:cms.spec.index');
//    Route::get('edit/{id}', [SpecCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.spec.edit');
//    Route::post('edit/{id}', [SpecCtrl::class, 'update']);
    Route::get('create', [SpecCtrl::class, 'create'])->name('create')->middleware('permission:cms.spec.create');
    Route::post('create', [SpecCtrl::class, 'store']);
//    Route::get('delete/{id}', [SpecCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.spec.delete');
});
