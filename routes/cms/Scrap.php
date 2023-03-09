<?php

use App\Http\Controllers\Cms\Commodity\ScrapCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'scrap', 'as' => 'scrap.'], function () {
    Route::get('', [ScrapCtrl::class, 'index'])->name('index')->middleware('permission:cms.scrap.index');
    Route::get('create', [ScrapCtrl::class, 'create'])->name('create')->middleware('permission:cms.scrap.create');
    Route::post('create', [ScrapCtrl::class, 'store']);
    Route::get('edit/{id}', [ScrapCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.scrap.edit');
    Route::post('edit/{id}', [ScrapCtrl::class, 'update']);
    Route::get('print_scrap/{id}', [ScrapCtrl::class, 'printScrap'])->name('print_scrap')->middleware('permission:cms.scrap.edit');
    Route::get('delete/{id}', [ScrapCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.scrap.delete');

});
