<?php

use App\Http\Controllers\Cms\ProductCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'product','as'=>'product.'], function () {
    Route::get('', [ProductCtrl::class, 'index'])->name('index');//->middleware('permission:cms.product.index');
    Route::get('edit/{id}', [ProductCtrl::class, 'edit'])->name('edit');//->middleware('permission:cms.product.edit');
    Route::post('edit/{id}', [ProductCtrl::class, 'update']);
    Route::get('create', [ProductCtrl::class, 'create'])->name('create');//->middleware('permission:cms.product.create');
    Route::post('create', [ProductCtrl::class, 'store']);
    Route::get('delete/{id}', [ProductCtrl::class, 'destroy'])->name('delete');//->middleware('permission:cms.product.delete');
});
