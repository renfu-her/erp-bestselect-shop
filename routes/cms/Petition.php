<?php

use App\Http\Controllers\Cms\AdminManagement\PetitionCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'petition', 'as' => 'petition.'], function () {
    Route::get('', [PetitionCtrl::class, 'index'])->name('index')->middleware('permission:cms.petition.index');
    Route::get('create', [PetitionCtrl::class, 'create'])->name('create')->middleware('permission:cms.petition.create');
    Route::post('create', [PetitionCtrl::class, 'store']);
    Route::get('edit/{id}', [PetitionCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.petition.edit');
    Route::post('edit/{id}', [PetitionCtrl::class, 'update']);
    Route::get('delete/{id}', [PetitionCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.petition.delete');
    Route::get('show/{id}', [PetitionCtrl::class, 'show'])->name('show');

});
