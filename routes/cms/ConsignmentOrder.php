<?php

use App\Http\Controllers\Cms\Commodity\ConsignmentOrderCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'consignment-order', 'as' => 'consignment-order.'], function () {
    Route::get('', [ConsignmentOrderCtrl::class, 'index'])->name('index')->middleware('permission:cms.consignment-order.index');

    Route::get('create', [ConsignmentOrderCtrl::class, 'create'])->name('create')->middleware('permission:cms.consignment-order.create');
    Route::post('create', [ConsignmentOrderCtrl::class, 'store']);
    Route::get('order_edit/{id}', [ConsignmentOrderCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.consignment-order.edit');
    Route::post('order_edit/{id}', [ConsignmentOrderCtrl::class, 'update']);
    Route::get('print_order_ship/{id}', [ConsignmentOrderCtrl::class, 'print_order_ship'])->name('print_order_ship')->middleware('permission:cms.consignment-order.edit');

    Route::get('log/{id}', [ConsignmentOrderCtrl::class, 'historyLog'])->name('log')->middleware('permission:cms.consignment-order.index');
});
