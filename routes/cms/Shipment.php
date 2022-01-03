<?php

use App\Http\Controllers\Cms\ShipmentCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'shipment','as'=>'shipment.'], function () {
    Route::get('', [ShipmentCtrl::class, 'index'])->name('index')->middleware('permission:cms.shipment.index');
    Route::get('edit/{id}', [ShipmentCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.shipment.edit');
    Route::post('edit/{id}', [ShipmentCtrl::class, 'update']);
    Route::get('create', [ShipmentCtrl::class, 'create'])->name('create')->middleware('permission:cms.shipment.create');
    Route::post('create', [ShipmentCtrl::class, 'store']);
    Route::get('delete/{id}', [ShipmentCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.shipment.delete');
});
