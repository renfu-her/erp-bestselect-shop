<?php

use App\Http\Controllers\Cms\Settings\ErpTravelCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'erp-travel', 'as' => 'erp-travel.'], function () {
    Route::get('', [ErpTravelCtrl::class, 'index'])->name('index')->middleware('permission:cms.erp-travel.index');
    Route::get('create', [ErpTravelCtrl::class, 'create'])->name('create')->middleware('permission:cms.erp-travel.create');
    Route::post('create', [ErpTravelCtrl::class, 'store']);
    Route::get('edit/{id}', [ErpTravelCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.erp-travel.edit');
    Route::post('edit/{id}', [ErpTravelCtrl::class, 'update']);
    Route::get('delete/{id}', [ErpTravelCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.erp-travel.delete');
});
