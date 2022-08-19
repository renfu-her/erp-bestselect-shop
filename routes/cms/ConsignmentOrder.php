<?php

use App\Http\Controllers\Cms\Commodity\ConsignmentOrderCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'consignment-order', 'as' => 'consignment-order.'], function () {
    Route::get('', [ConsignmentOrderCtrl::class, 'index'])->name('index')->middleware('permission:cms.consignment_order.index');

    Route::get('create', [ConsignmentOrderCtrl::class, 'create'])->name('create')->middleware('permission:cms.consignment_order.create');
    Route::post('create', [ConsignmentOrderCtrl::class, 'store']);
    Route::get('order_edit/{id}', [ConsignmentOrderCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.consignment_order.edit');
    Route::post('order_edit/{id}', [ConsignmentOrderCtrl::class, 'update']);

    Route::get('log/{id}', [ConsignmentOrderCtrl::class, 'historyLog'])->name('log')->middleware('permission:cms.consignment_order.index');
});
