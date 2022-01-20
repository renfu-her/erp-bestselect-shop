<?php

use App\Http\Controllers\Cms\AuthCtrl;
use App\Http\Controllers\Cms\DashboardCtrl;
use App\Http\Controllers\Cms\StyleDemo;
use Illuminate\Support\Facades\Route;
use NunoMaduro\Collision\Adapters\Phpunit\Style;

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
    require base_path('routes/cms/Role.php');
    require base_path('routes/cms/Permission.php');
    require base_path('routes/cms/Purchase.php');
    require base_path('routes/cms/ComboPurchase.php');
    require base_path('routes/cms/Depot.php');
    require base_path('routes/cms/Spec.php');
    require base_path('routes/cms/Stock.php');
    require base_path('routes/cms/NaviNode.php');


});
