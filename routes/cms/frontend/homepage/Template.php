<?php

use App\Http\Controllers\Cms\Frontend\Homepage\TemplateCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'template', 'as' => 'template.'], function () {
    Route::get('', [TemplateCtrl::class, 'index'])->name('index')->middleware('permission:cms.homepage.banner.index');
    Route::post('sort', [TemplateCtrl::class, 'sort'])->name('sort');
    Route::get('create', [TemplateCtrl::class, 'create'])->name('create')->middleware('permission:cms.homepage.edit');
     Route::post('create', [TemplateCtrl::class, 'store']);
    Route::get('edit/{id}', [TemplateCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.homepage.edit');
     Route::post('edit/{id}', [TemplateCtrl::class, 'update']);
    Route::get('delete/{id}', [TemplateCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.homepage.edit');
});
