<?php

use App\Http\Controllers\Api\Web\OnePageCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'one-page', 'as' => 'one-page'], function () {
    Route::get('get-page/{id}', [OnePageCtrl::class, 'getPage']);
    Route::post('get-url', [OnePageCtrl::class, 'getUrl']);
    Route::post('list', [OnePageCtrl::class, 'list']);
});
