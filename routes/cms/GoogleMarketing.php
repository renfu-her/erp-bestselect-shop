<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\Marketing\GoogleMarketingCtrl;

Route::group(['prefix' => 'google_marketing', 'as' => 'google_marketing.'], function () {
    Route::get('', [GoogleMarketingCtrl::class, 'index'])->name('index')->middleware('permission:cms.google_marketing.index');
//    Route::get('create', [GoogleMarketingCtrl::class, 'create'])->name('create')->middleware('permission:cms.google_marketing.create');
    Route::get('create_ads_events', [GoogleMarketingCtrl::class, 'createGoogleAdsEvents'])->name('create_ads_events')->middleware('permission:cms.google_ads_events.create_ads_events');
    Route::get('edit_ads_events/{id}', [GoogleMarketingCtrl::class, 'editGoogleAdsEvents'])->name('edit_ads_events')->middleware('permission:cms.google_ads_events.edit_ads_events');
    Route::post('store_ads_events', [GoogleMarketingCtrl::class, 'storeGoogleAdsEvents'])->name('store_ads_events');
    Route::post('create', [GoogleMarketingCtrl::class, 'store']);
    Route::get('edit/{id}', [GoogleMarketingCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.google_marketing.edit');
    Route::post('edit/{id}', [GoogleMarketingCtrl::class, 'update']);
    Route::get('delete/{id}', [GoogleMarketingCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.google_marketing.delete');
});
