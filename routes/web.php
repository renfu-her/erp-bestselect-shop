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
    require base_path('routes/cms/PayableDefault.php');
    require base_path('routes/cms/ReceivedDefault.php');
    require base_path('routes/cms/Ap.php');
    require base_path('routes/cms/Ar.php');
    require base_path('routes/cms/FirstGrade.php');
    require base_path('routes/cms/Delivery.php');
    require base_path('routes/cms/Logistic.php');
    require base_path('routes/cms/UserMnt.php');

    require base_path('routes/cms/Discount.php');
    require base_path('routes/cms/PromoCoupon.php');
    require base_path('routes/cms/GoogleMarketing.php');
    require base_path('routes/cms/Consignment.php');
});

Route::group(['prefix' => 'customer', 'as' => 'customer.', 'middleware' => 'guest:customer'], function () {

    Route::get('/forgot-password', [CustomerResetCtrl::class, 'forgotPassword'])->name('password.request');
    Route::post('/forgot-password', [CustomerResetCtrl::class, 'sendResetPwMail'])->name('password.email');

    Route::get('/reset-password/{token?}', [CustomerResetCtrl::class, 'resetPassword'])->name('password.reset');
    Route::post('/reset-password', [CustomerResetCtrl::class, 'resetPasswordStore'])->name('password.update');

    Route::get('/login-reset-status', [CustomerResetCtrl::class, 'loginResetStatus'])->name('login-reset-status');
});
