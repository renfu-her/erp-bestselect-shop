<?php

use App\Http\Controllers\Cms\Commodity\InboundFix0917ImportCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'inbound_fix0917_import','as'=>'inbound_fix0917_import.'], function () {
    Route::get('', [InboundFix0917ImportCtrl::class, 'index'])->name('index')->middleware('permission:cms.inbound_fix0917_import.index');
    Route::post('compare_old_to_diff_new_stock', [InboundFix0917ImportCtrl::class, 'compare_old_to_diff_new_stock'])->name('compare_old_to_diff_new_stock')->middleware('permission:cms.inbound_fix0917_import.index');

    Route::get('import_no_delivery', [InboundFix0917ImportCtrl::class, 'import_no_delivery_page'])->name('import_no_delivery')->middleware('permission:cms.inbound_fix0917_import.index');
    Route::get('import_has_delivery', [InboundFix0917ImportCtrl::class, 'import_has_delivery_page'])->name('import_has_delivery')->middleware('permission:cms.inbound_fix0917_import.index');
    Route::get('del_purchase_diff_item\{purchaseID}', [InboundFix0917ImportCtrl::class, 'del_purchase_diff_item'])->name('del_purchase_diff_item')->middleware('permission:cms.inbound_fix0917_import.edit');
    Route::post('del_multi_purchase_diff_item', [InboundFix0917ImportCtrl::class, 'del_multi_purchase_diff_item'])->name('del_multi_purchase_diff_item')->middleware('permission:cms.inbound_fix0917_import.edit');

    Route::get('recovery_purchase_1011', [InboundFix0917ImportCtrl::class, 'recovery_purchase_1011'])->name('recovery_purchase_1011')->middleware('permission:cms.inbound_fix0917_import.edit');
});
