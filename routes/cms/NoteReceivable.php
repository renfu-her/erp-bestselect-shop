<?php

use App\Http\Controllers\Cms\AccountManagement\NoteReceivableCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'note_receivable', 'as' => 'note_receivable.'], function () {
    Route::get('', [NoteReceivableCtrl::class, 'index'])->name('index')->middleware('permission:cms.note_receivable.index');
    Route::get('record/{id}', [NoteReceivableCtrl::class, 'record'])->name('record')->middleware('permission:cms.note_receivable.show');

    Route::match(['get', 'post'], 'ask/{type}', [NoteReceivableCtrl::class, 'ask'])->name('ask')->where(['type' => '(collection|nd|cashed)'])->middleware('permission:cms.note_receivable.edit');
    Route::get('detail/{type}', [NoteReceivableCtrl::class, 'detail'])->name('detail')->where(['type' => '(collection|nd|cashed)'])->middleware('permission:cms.note_receivable.show');

    Route::get('reverse/{id}', [NoteReceivableCtrl::class, 'reverse'])->name('reverse')->middleware('permission:cms.note_receivable.edit');
});
