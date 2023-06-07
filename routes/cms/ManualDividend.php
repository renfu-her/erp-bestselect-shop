<?php

use App\Http\Controllers\Cms\Marketing\ManualDividendCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manual-dividend', 'as' => 'manual-dividend.', 'middleware' => ['permission:cms.manual-dividend.index']], function () {
    Route::get('', [ManualDividendCtrl::class, 'index'])->name('index');
    Route::get('create', [ManualDividendCtrl::class, 'create'])->name('create');
    Route::post('create', [ManualDividendCtrl::class, 'store']);
    Route::get('show/{id}', [ManualDividendCtrl::class, 'show'])->name('show');

    Route::get('sample', [ManualDividendCtrl::class, 'sample'])->name('sample');

});
