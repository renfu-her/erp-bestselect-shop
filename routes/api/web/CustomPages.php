<?php

use App\Http\Controllers\Api\Web\CustomPagesCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'custom-pages', 'as' => 'custom-pages.'], function () {
    Route::post('id', [CustomPagesCtrl::class, "getData"]);
});
