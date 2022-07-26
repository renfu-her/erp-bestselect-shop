<?php

use App\Http\Controllers\Cms\Frontend\CustomPagesCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'custom-pages','as'=>'custom-pages.'], function () {
    Route::get('', [CustomPagesCtrl::class, 'index'])->name('index');
    Route::get('create', [CustomPagesCtrl::class, 'create'])->name('create');
    Route::post('create', [CustomPagesCtrl::class, 'store']);
    Route::get('edit/{id}', [CustomPagesCtrl::class, 'edit'])->name('edit');
    Route::post('edit/{id}', [CustomPagesCtrl::class, 'update']);
    Route::get('delete/{id}', [CustomPagesCtrl::class, 'destroy'])->name('delete');
});
