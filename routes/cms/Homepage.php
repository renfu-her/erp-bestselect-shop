<?php

use App\Http\Controllers\Cms\Frontend\HomepageCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'homepage','as'=>'homepage.'], function () {
    Route::get('', [HomepageCtrl::class, 'index'])->name('index')->middleware('permission:cms.homepage.index');
    require base_path('routes/cms/frontend/homepage/Banner.php');
});
