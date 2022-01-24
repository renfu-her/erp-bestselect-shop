<?php

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

Route::group(['prefix' => 'user', 'as' => 'user.', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/tokens/delete-all', function (Request $request) {
        // $token = $request->user()->createToken($request->token_name);
        // dd($request->token_name);
        $request->user()->tokens()->delete();
        return 'ok';
    });

    Route::get('/tokens/delete-current', function (Request $request) {
        // $token = $request->user()->createToken($request->token_name);
        // dd($request->token_name);
        $request->user()->currentAccessToken()->delete();
        return 'ok';
    });

    Route::get('/user', function (Request $request) {

        return $request->user();
    });



});

Route::group(['prefix' => 'cms', 'as' => 'cms.', 'middleware' => 'auth:cms-api'], function () {
    require base_path('routes/api/Product.php');
});

require base_path('routes/api/Addr.php');
require base_path('routes/api/Home.php');
