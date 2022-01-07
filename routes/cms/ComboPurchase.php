<?php

use App\Http\Controllers\Cms\Commodity\ComboPurchaseCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'combo-purchase', 'as' => 'combo-purchase.'], function () {
    Route::get('', [ComboPurchaseCtrl::class, 'index'])->name('index');
    Route::get('edit/{id}/{sid}', [ComboPurchaseCtrl::class, 'edit'])->name('edit');
    Route::post('edit/{id}/{sid}', [ComboPurchaseCtrl::class, 'update']);

});
