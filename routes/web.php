<?php

use App\Http\Controllers\Cms\AuthCtrl;
use App\Http\Controllers\Cms\CustomerResetCtrl;
use App\Http\Controllers\Cms\DashboardCtrl;
use App\Http\Controllers\Cms\StyleDemo;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */


Route::get('/', function () {
    // dd(app('url')->route('test',[],false));
    return redirect()->route('cms.dashboard');
});

Route::get('/demo', StyleDemo::class)->name('cms.styleDemo');

Route::get('/login', [AuthCtrl::class, 'login'])->name('cms.login');
Route::post('/login', [AuthCtrl::class, 'authenticate']);
Route::get('/logout', [AuthCtrl::class, 'logout'])->name('cms.logout');

Route::group(['prefix' => 'cms', 'as' => 'cms.', 'middleware' => 'auth:user'], function () {
    Route::get('dashboard', DashboardCtrl::class)->name('dashboard');
    require base_path('routes/cms/Product.php');
    require base_path('routes/cms/Category.php');
    require base_path('routes/cms/Collection.php');
    require base_path('routes/cms/Supplier.php');
    require base_path('routes/cms/SaleChannel.php');
    require base_path('routes/cms/Shipment.php');
    require base_path('routes/cms/User.php');
    require base_path('routes/cms/Customer.php');
    require base_path('routes/cms/Role.php');
    require base_path('routes/cms/Permission.php');
    require base_path('routes/cms/Purchase.php');
    require base_path('routes/cms/ComboPurchase.php');
    require base_path('routes/cms/Depot.php');
    require base_path('routes/cms/Spec.php');
    require base_path('routes/cms/Stock.php');
    require base_path('routes/cms/Homepage.php');
    require base_path('routes/cms/NaviNode.php');
    require base_path('routes/cms/Order.php');
    require base_path('routes/cms/GeneralLedger.php');
    require base_path('routes/cms/IncomeStatement.php');
    require base_path('routes/cms/FirstGrade.php');
});


Route::group(['middleware' => 'guest:customer'], function () {
    Route::get('/forgot-password', [CustomerResetCtrl::class, 'forgot_password'])->name('password.request');
    Route::post('/forgot-password', [CustomerResetCtrl::class, 'send_reset_pw_mail'])->name('password.email');

    Route::get('/reset-password/{token?}', [CustomerResetCtrl::class, 'reset_password'])->name('password.reset');
    Route::post('/reset-password', [CustomerResetCtrl::class, 'reset_password_store'])->name('password.update');

    Route::get('/login-test', function () {
        return session('status');
    })->name('login');
});




