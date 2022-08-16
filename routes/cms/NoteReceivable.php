<?php

use App\Http\Controllers\Cms\AccountManagement\NoteReceivableCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'note_receivable', 'as' => 'note_receivable.'], function () {
    Route::get('', [NoteReceivableCtrl::class, 'index'])->name('index')->middleware('permission:cms.note_receivable.index');
    Route::get('record/{id}', [NoteReceivableCtrl::class, 'record'])->name('record')->middleware('permission:cms.note_receivable.record');

    Route::match(['get', 'post'], 'ask/{type}', [NoteReceivableCtrl::class, 'ask'])->name('ask')->middleware('permission:cms.note_receivable.ask')->where(['type' => '(collection|nd|cashed)']);
    Route::get('detail/{type}', [NoteReceivableCtrl::class, 'detail'])->name('detail')->where(['type' => '(collection|nd|cashed)']);

    Route::get('reverse/{id}', [NoteReceivableCtrl::class, 'reverse'])->name('reverse')->middleware('permission:cms.note_receivable.reverse');
});
