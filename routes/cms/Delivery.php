<?php

use App\Http\Controllers\Cms\Commodity\DeliveryCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'delivery','as'=>'delivery.'], function () {
    Route::get('', [DeliveryCtrl::class, 'index'])->name('index')->middleware('permission:cms.delivery.index');
    Route::get('edit/{groupId}', [DeliveryCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.delivery.edit');
    Route::post('edit/{groupId}', [DeliveryCtrl::class, 'update']);
    Route::get('create', [DeliveryCtrl::class, 'create'])->name('create')->middleware('permission:cms.delivery.create');
    Route::post('create', [DeliveryCtrl::class, 'store']);
    Route::get('delete/{groupId}', [DeliveryCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.delivery.delete');
});
