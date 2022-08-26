<?php

use App\Http\Controllers\Cms\Settings\MailSetCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'mail_set','as'=>'mail_set.'], function () {
    Route::get('', [MailSetCtrl::class, 'index'])->name('index')->middleware('permission:cms.mail_set.index');
    Route::post('edit', [MailSetCtrl::class, 'update'])->name('edit')->middleware('permission:cms.mail_set.edit');
});
