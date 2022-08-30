<?php

use App\Http\Controllers\Cms\Frontend\Homepage\NavbarCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'navbar', 'as' => 'navbar.'], function () {
    Route::get('', [NavbarCtrl::class, 'index'])->name('index')->middleware('permission:cms.homepage.banner.index');
});
