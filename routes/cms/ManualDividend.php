<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\Marketing\ManualDividendCtrl;

Route::group(['prefix' => 'manual-dividend', 'as' => 'manual-dividend.'], function () {
    Route::get('', [ManualDividendCtrl::class, 'index'])->name('index')->middleware('permission:cms.manual-dividend.index');
    Route::get('create', [ManualDividendCtrl::class, 'create'])->name('create')->middleware('permission:cms.manual-dividend.create');
    Route::post('create', [ManualDividendCtrl::class, 'store']);
    Route::get('show/{id}', [ManualDividendCtrl::class, 'show'])->name('show');

    Route::get('sample', [ManualDividendCtrl::class, 'sample'])->name('sample');

});
