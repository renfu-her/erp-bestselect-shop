<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\SupplierCtrl;

Route::group(['prefix' => 'supplier', 'as' => 'supplier.'], function () {
    Route::get('', [SupplierCtrl::class, 'index'])->name('index');
    Route::get('create', [SupplierCtrl::class, 'create'])->name('create');
    Route::post('create', [SupplierCtrl::class, 'store']);
    Route::get('edit/{id}', [SupplierCtrl::class, 'edit'])->name('edit');
    Route::post('edit/{id}', [SupplierCtrl::class, 'update']);
    Route::get('delete/{id}', [SupplierCtrl::class, 'destroy'])->name('delete');
});

