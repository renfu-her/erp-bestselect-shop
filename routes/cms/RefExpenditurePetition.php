<?php
use App\Http\Controllers\Cms\AdminManagement\RefExpenditurePetitionCtrl;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'ref_expenditure_petition', 'as' => 'ref_expenditure_petition.'], function () {
    Route::match(['get', 'post'], 'edit/{current_sn}', [RefExpenditurePetitionCtrl::class, 'edit'])->name('edit');
});