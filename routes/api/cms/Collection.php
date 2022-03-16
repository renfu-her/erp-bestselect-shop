<?php

use App\Http\Controllers\Api\Cms\Commodity\CollectionCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'collection', 'as' => 'collection.'], function () {
    Route::get('get', [CollectionCtrl::class, 'getCollections'])->name('get-collections');
  //  Route::post('store-receive-depot', [CollectionCtrl::class, 'store'])->name('create-receive-depot');
  //  Route::get('del-receive-depot/{receiveDepotId}', [CollectionCtrl::class, 'destroy'])->name('del-receive-depot');
});
