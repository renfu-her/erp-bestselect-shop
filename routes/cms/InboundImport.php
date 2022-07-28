<?php

use App\Http\Controllers\Cms\Commodity\InboundImportCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'inbound_import','as'=>'inbound_import.'], function () {
    Route::get('', [InboundImportCtrl::class, 'index'])->name('index')->middleware('permission:cms.inbound_import.index');
    Route::post('upload_excel', [InboundImportCtrl::class, 'uploadExcel'])->name('upload_excel')->middleware('permission:cms.inbound_import.create');
    Route::get('import_log', [InboundImportCtrl::class, 'import_log'])->name('import_log')->middleware('permission:cms.inbound_import.index');
});
