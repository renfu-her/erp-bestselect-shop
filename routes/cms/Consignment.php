<?php

use App\Http\Controllers\Cms\Commodity\ConsignmentCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'consignment', 'as' => 'consignment.'], function () {
    Route::get('', [ConsignmentCtrl::class, 'index'])->name('index')->middleware('permission:cms.consignment.index');
    Route::get('create', [ConsignmentCtrl::class, 'create'])->name('create')->middleware('permission:cms.consignment.create');
    Route::post('create', [ConsignmentCtrl::class, 'store']);
    Route::get('edit/{id}', [ConsignmentCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.consignment.edit');
//    Route::post('edit/{id}', [ConsignmentCtrl::class, 'update']);
//    Route::get('edit/{id}/pay-deposit', [ConsignmentCtrl::class, 'payDeposit'])->name('pay-deposit');
////    Route::get('edit/{id}/pay-final', [ConsignmentCtrl::class, 'payFinal'])->name('pay-final');
//    Route::post('pay-order/{id}', [ConsignmentCtrl::class, 'payOrder'])->name('pay-order')->middleware('permission:cms.consignment.pay-order');
//    Route::get('pay-order/{id}', [ConsignmentCtrl::class, 'payOrder'])->name('view-pay-order')->middleware('permission:cms.consignment.view-pay-order');
//    Route::get('delete/{id}', [ConsignmentCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.consignment.delete');
    Route::post('close/{id}', [ConsignmentCtrl::class, 'close'])->name('close')->middleware('permission:cms.consignment.close');
//
    Route::get('inbound/{id}', [ConsignmentCtrl::class, 'inbound'])->name('inbound')->middleware('permission:cms.consignment.inbound');
    Route::post('store_inbound/{id}', [ConsignmentCtrl::class, 'storeInbound'])->name('store_inbound');
    Route::get('delete_inbound/{id}', [ConsignmentCtrl::class, 'deleteInbound'])->name('delete_inbound')->middleware('permission:cms.consignment.delete_inbound');
//
//    Route::get('log/{id}', [ConsignmentCtrl::class, 'historyLog'])->name('log')->middleware('permission:cms.consignment.historyLog');
});
