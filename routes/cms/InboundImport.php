<?php

use App\Http\Controllers\Cms\Commodity\InboundImportCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'inbound_import','as'=>'inbound_import.'], function () {
    Route::get('', [InboundImportCtrl::class, 'index'])->name('index')->middleware('permission:cms.inbound_import.index');
    Route::post('upload_excel', [InboundImportCtrl::class, 'uploadExcel'])->name('upload_excel')->middleware('permission:cms.inbound_import.edit');
    Route::get('import_log', [InboundImportCtrl::class, 'import_log'])->name('import_log')->middleware('permission:cms.inbound_import.index');

    Route::get('inbound_list', [InboundImportCtrl::class, 'inbound_list'])->name('inbound_list')->middleware('permission:cms.inbound_import.index');
    Route::get('inbound_edit\{inboundId}', [InboundImportCtrl::class, 'inbound_edit'])->name('inbound_edit')->middleware('permission:cms.inbound_import.edit');
    Route::post('inbound_edit_store\{inboundId}', [InboundImportCtrl::class, 'inbound_edit_store'])->name('inbound_edit_store')->middleware('permission:cms.inbound_import.edit');
    Route::get('inbound_log', [InboundImportCtrl::class, 'inbound_log'])->name('inbound_log')->middleware('permission:cms.inbound_import.index');

    Route::get('compare_old_to_diff_new_stock', [InboundImportCtrl::class, 'compare_old_to_diff_new_stock_page'])->name('compare_old_to_diff_new_stock')->middleware('permission:cms.inbound_import.index');
    Route::post('compare_old_to_diff_new_stock', [InboundImportCtrl::class, 'compare_old_to_diff_new_stock_todo']);

    Route::get('import_no_delivery', [InboundImportCtrl::class, 'import_no_delivery_page'])->name('import_no_delivery')->middleware('permission:cms.inbound_import.index');
    Route::get('import_has_delivery', [InboundImportCtrl::class, 'import_has_delivery_page'])->name('import_has_delivery')->middleware('permission:cms.inbound_import.index');
    Route::get('del_purchase\{purchaseID}', [InboundImportCtrl::class, 'del_purchase'])->name('del_purchase')->middleware('permission:cms.inbound_import.edit');
    Route::post('del_multi_purchase', [InboundImportCtrl::class, 'del_multi_purchase'])->name('del_multi_purchase')->middleware('permission:cms.inbound_import.edit');
});
