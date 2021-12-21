<?php

use App\Http\Controllers\Cms\CategoryController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'category','as'=>'category.'], function () {
    Route::get('', [CategoryController::class, 'index'])->name('index')->middleware('permission:cms.category.index');
    Route::get('edit/{id}', [CategoryController::class, 'edit'])->name('edit')->middleware('permission:cms.category.edit');
    Route::post('edit/{id}', [CategoryController::class, 'update']);
    Route::get('create', [CategoryController::class, 'create'])->name('create')->middleware('permission:cms.category.create');
    Route::post('create', [CategoryController::class, 'store']);
    Route::get('delete/{id}', [CategoryController::class, 'destroy'])->name('delete')->middleware('permission:cms.category.delete');
});
