<?php

use Illuminate\Http\Request;

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

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user()->id;
});


Route::middleware('auth:api')->get('/logout', function (Request $request) {

    $request->user()->token()->revoke();

    return '退出登录成功';
});

//v1 路由
Route::group(['prefix' => 'v1', 'namespace' => 'Api\V1'], function () {
    Route::get('user', 'UserController@user');


    Route::group(['prefix' => 'user'], function () {
        Route::post('user/login', 'UserController@login');
        Route::post('register', 'UserController@register');
    });

    Route::group(['prefix' => 'user', 'middleware' => 'auth:api'], function () {
        Route::get('get_profile', 'UserController@getProfile');
    });
});

