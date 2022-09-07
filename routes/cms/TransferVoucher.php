<?php

use App\Http\Controllers\Cms\AccountManagement\TransferVoucherCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'transfer_voucher', 'as' => 'transfer_voucher.'], function () {
    Route::get('', [TransferVoucherCtrl::class, 'index'])->name('index')->middleware('permission:cms.transfer_voucher.index');
    Route::get('show/{id}', [TransferVoucherCtrl::class, 'show'])->name('show')->middleware('permission:cms.transfer_voucher.show');
    Route::match(['get', 'post'], 'create', [TransferVoucherCtrl::class, 'create'])->name('create')->middleware('permission:cms.transfer_voucher.create');
    Route::match(['get', 'post'], 'edit/{id}', [TransferVoucherCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.transfer_voucher.edit');
    Route::get('delete/{id}', [TransferVoucherCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.transfer_voucher.delete');
});
