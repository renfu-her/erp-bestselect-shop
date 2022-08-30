<?php

use App\Http\Controllers\Cms\Commodity\ComboPurchaseCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'combo-purchase', 'as' => 'combo-purchase.'], function () {
    Route::get('', [ComboPurchaseCtrl::class, 'index'])->name('index')->middleware('permission:cms.combo-purchase.index');
    Route::get('edit/{id}', [ComboPurchaseCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.combo-purchase.edit');
    Route::post('edit/{id}', [ComboPurchaseCtrl::class, 'update']);

});
