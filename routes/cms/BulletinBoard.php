<?php

use App\Http\Controllers\Cms\AdminManagement\BulletinBoardCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'bulletin_board', 'as' => 'bulletin_board.'], function () {
    Route::get('', [BulletinBoardCtrl::class, 'index'])->name('index')->middleware('permission:cms.bulletin_board.index');
    Route::get('create', [BulletinBoardCtrl::class, 'create'])->name('create')->middleware('permission:cms.bulletin_board.create');
    Route::post('create', [BulletinBoardCtrl::class, 'store']);
    Route::get('show/{id}', [BulletinBoardCtrl::class, 'show'])->name('show')->middleware('permission:cms.bulletin_board.show');
    Route::get('edit/{id}', [BulletinBoardCtrl::class, 'edit'])->name('edit')->middleware('permission:cms.bulletin_board.edit');
    Route::post('edit/{id}', [BulletinBoardCtrl::class, 'update']);
    Route::get('delete/{id}', [BulletinBoardCtrl::class, 'destroy'])->name('delete')->middleware('permission:cms.bulletin_board.delete');
});
