<?php

use App\Http\Controllers\Cms\Frontend\Homepage\TemplateCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'template', 'as' => 'template.'], function () {
    Route::get('', [TemplateCtrl::class, 'index'])->name('index');
});
