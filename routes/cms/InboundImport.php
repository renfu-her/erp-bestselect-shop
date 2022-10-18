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

    //個別補採購入庫
    Route::get('import_pcs_miss_sku', [InboundImportCtrl::class, 'import_pcs_miss_sku'])->name('import_pcs_miss_sku')->middleware('permission:cms.inbound_import.index');
    Route::post('upload_xls_pcs_miss_sku', [InboundImportCtrl::class, 'upload_xls_pcs_miss_sku'])->name('upload_xls_pcs_miss_sku')->middleware('permission:cms.inbound_import.index');
});
