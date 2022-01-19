<?php

use App\Http\Controllers\Cms\Frontend\HomepageCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'homepage','as'=>'homepage.'], function () {
    require base_path('routes/cms/frontend/homepage/Navbar.php');
    require base_path('routes/cms/frontend/homepage/Banner.php');
    require base_path('routes/cms/frontend/homepage/Template.php');
});
