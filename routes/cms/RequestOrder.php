<?php

use App\Http\Controllers\Cms\AccountManagement\RequestOrderCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'request', 'as' => 'request.'], function () {
    Route::get('', [RequestOrderCtrl::class, 'index'])->name('index')->middleware('permission:cms.request.index');
    Route::match(['get', 'post'], 'create', [RequestOrderCtrl::class, 'create'])->name('create')->middleware('permission:cms.request.create');
    Route::get('show/{id}', [RequestOrderCtrl::class, 'show'])->name('show')->middleware('permission:cms.request.show');

    Route::get('ro_edit/{id}', [RequestOrderCtrl::class, 'ro_edit'])->name('ro-edit');
    Route::post('ro_store/{id}', [RequestOrderCtrl::class, 'ro_store'])->name('ro-store');
    Route::get('ro_receipt/{id}', [RequestOrderCtrl::class, 'ro_receipt'])->name('ro-receipt')->middleware('permission:cms.request.ro-receipt');
    Route::match(['get', 'post'], 'ro_review/{id}', [RequestOrderCtrl::class, 'ro_review'])->name('ro-review')->middleware('permission:cms.request.ro-review');
    Route::match(['get', 'post'], 'ro_taxation/{id}', [RequestOrderCtrl::class, 'ro_taxation'])->name('ro-taxation')->middleware('permission:cms.request.ro-taxation');
});
