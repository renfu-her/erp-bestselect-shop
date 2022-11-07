<?php

use App\Http\Controllers\Cms\AdminManagement\PetitionCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'petition', 'as' => 'petition.'], function () {
    Route::get('', [PetitionCtrl::class, 'index'])->name('index')->middleware('permission:cms.petition.index');
    Route::get('create', [PetitionCtrl::class, 'create'])->name('create')->middleware('permission:cms.petition.index');
    Route::post('create', [PetitionCtrl::class, 'store']);
    Route::get('edit/{id}', [PetitionCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.petition.index');
    Route::post('edit/{id}', [PetitionCtrl::class, 'update']);
    Route::get('delete/{id}', [PetitionCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.petition.index');
    Route::get('show/{id}', [PetitionCtrl::class, 'show'])->name('show');

    Route::get('audit-list', [PetitionCtrl::class, 'auditList'])->name('audit-list');
    Route::get('audit-confirm/{id}', [PetitionCtrl::class, 'auditEdit'])->name('audit-confirm');
    Route::post('audit-confirm/{id}', [PetitionCtrl::class, 'auditConfirm']);

});
