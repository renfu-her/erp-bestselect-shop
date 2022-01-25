<?php

use App\Http\Controllers\Api\Web\CollectionCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'collection', 'as' => 'collection.'], function () {
    Route::post('collection', [CollectionCtrl::class, 'collection'])->name('get-collection');
});
