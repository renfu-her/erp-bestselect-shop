<?php

use App\Http\Controllers\Cms\AccountManagement\OrderInvoiceManagerCtrl;
use Illuminate\Support\Facades\Route;

/**
 * 請款比例
 */
Route::group(['prefix' => 'order_invoice_manager', 'as' => 'order_invoice_manager.'], function () {
    Route::get('', [OrderInvoiceManagerCtrl::class, 'index'])->name('index')->middleware('permission:cms.order_invoice_manager.index');
    Route::get('month', [OrderInvoiceManagerCtrl::class, 'month'])->name('month')->middleware('permission:cms.order_invoice_manager.index');
    Route::post('export_excel_month', [OrderInvoiceManagerCtrl::class, 'export_excel_month'])->name('export_excel_month')->middleware('permission:cms.order_invoice_manager.export_excel_month');
});
