<?php

use App\Http\Controllers\Cms\Settings\HomepageCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'homepage','as'=>'homepage.'], function () {
    Route::get('', [HomepageCtrl::class, 'index'])->name('index')->middleware('permission:cms.homepage.index');
});
