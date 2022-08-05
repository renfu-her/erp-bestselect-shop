<?php

use App\Http\Controllers\Api\Cms\Commodity\DiscountCtrl;
use App\Http\Controllers\Api\CustomerCtrl;
use App\Http\Controllers\Api\Web\NaviCtrl;
use App\Http\Controllers\Api\Web\OrderCtrl;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::get('/getip', function (Request $request) {
    return $_SERVER['REMOTE_ADDR'];
});

Route::get('/tokens/get', function (Request $request) {

    //  $token = User::where('id', 1)->get()->first()->tokens()->delete();
    $token = User::where('id', 1)->get()->first()->tokens()->plainTextToken();
    dd($token);
    return 'ok';
});

Route::get('/tokens/create', function (Request $request) {
    // $token = $request->user()->createToken($request->token_name);
    // dd($request->token_name);
    $token = User::where('id', 1)->get()->first()->createToken('ddd');
    return ['token' => $token->plainTextToken];
});

Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {
    Route::post('register', [CustomerCtrl::class, 'register']);
    Route::post('login', [CustomerCtrl::class, 'login']);
});

Route::group(['prefix' => 'customer', 'as' => 'customer.', 'middleware' => ['auth:sanctum', 'identity.api.customer']], function () {
    Route::get('/info', [CustomerCtrl::class, 'customerInfo'])->name('customer_info');
    Route::post('/update-info', [CustomerCtrl::class, 'updateCustomerInfo'])->name('update_customer_info');

    Route::get('/address-list', [CustomerCtrl::class, 'customerAddress'])->name('customer_address');
    Route::post('/delete-address', [CustomerCtrl::class, 'deleteAddress'])->name('delete_address');
    Route::post('/edit-address', [CustomerCtrl::class, 'editAddress'])->name('edit_address');
    Route::post('/default-address', [CustomerCtrl::class, 'setDefaultAddress'])->name('default_address');
    Route::post('/order-list', [OrderCtrl::class, 'orderList'])->name('order_list');

    Route::get('/logout-all', [CustomerCtrl::class, 'tokensDeleteAll']);
    Route::get('/logout', [CustomerCtrl::class, 'tokensDeleteCurrent']);

    require base_path('routes/api/web/CustomerData.php');
    require base_path('routes/api/web/OrderWithAuth.php');


});

Route::group(['prefix' => 'cms', 'as' => 'cms.', 'middleware' => 'auth:cms-api'], function () {
    require base_path('routes/api/Depot.php');
    require base_path('routes/api/Product.php');
    require base_path('routes/api/cms/Delivery.php');
    require base_path('routes/api/cms/Collection.php');
    require base_path('routes/api/cms/Discount.php');
    require base_path('routes/api/cms/Logistic.php');
    require base_path('routes/api/User.php');
    require base_path('routes/api/cms/Order.php');


});

Route::group(['prefix' => 'web', 'as' => 'web.'], function () {
    Route::post('navi', NaviCtrl::class);
    require base_path('routes/api/web/Home.php');
    require base_path('routes/api/Collection.php');
    require base_path('routes/api/web/Collection.php');
    require base_path('routes/api/web/CustomPages.php');
    require base_path('routes/api/web/Product.php');
    require base_path('routes/api/web/Order.php');

    Route::post('check-discount-code', [DiscountCtrl::class, 'checkDiscountCode'])->name('check-discount-code');

    Route::post('check-recommender', [CustomerCtrl::class, 'checkRecommender'])->name('check-recommender');

    Route::post('checksum-test', ['uses' => function () {
        return 'ok';
    }, 'middleware' => 'checksum'], );
});

require base_path('routes/api/Addr.php');
require base_path('routes/api/Schedule.php');
require base_path('routes/api/Bank.php');

