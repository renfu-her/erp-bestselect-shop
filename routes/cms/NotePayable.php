<?php

use App\Http\Controllers\Cms\AccountManagement\NotePayableCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'note_payable', 'as' => 'note_payable.'], function () {
    Route::get('', [NotePayableCtrl::class, 'index'])->name('index')->middleware('permission:cms.note_payable.index');
    Route::get('record/{id}', [NotePayableCtrl::class, 'record'])->name('record')->middleware('permission:cms.note_payable.show');

    Route::match(['get', 'post'], 'ask/{type}', [NotePayableCtrl::class, 'ask'])->name('ask')->where(['type' => '(cashed)'])->middleware('permission:cms.note_payable.edit');
    Route::get('detail/{type}', [NotePayableCtrl::class, 'detail'])->name('detail')->where(['type' => '(cashed)'])->middleware('permission:cms.note_payable.show');

    Route::get('reverse/{id}', [NotePayableCtrl::class, 'reverse'])->name('reverse')->middleware('permission:cms.note_payable.edit');

    Route::match(['get', 'post'], 'checkbook', [NotePayableCtrl::class, 'checkbook'])->name('checkbook')->middleware('permission:cms.note_payable.edit');
});
