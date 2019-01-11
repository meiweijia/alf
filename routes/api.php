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

#region V1路由
Route::group(['prefix' => 'v1', 'namespace' => 'Api\V1'], function () {
    Route::get('user', 'UserController@user');


    Route::group(['prefix' => 'user'], function () {
        Route::any('login', 'UserController@login');
        Route::post('register', 'UserController@register');
        Route::get('wechat_auth', 'UserController@wechatAuth');
        Route::get('check_bind_mobile/{key}', 'UserController@checkBindMobile')->name('v1.user.check_bind_mobile');
        Route::any('oauth_callback','UserController@oauthCallback')->name('v1.user.oauth_callback');
    });

    Route::group(['prefix' => 'user','middleware' => 'auth:api'], function () {
        Route::post('register', 'UserController@register');
        Route::get('get_profile', 'UserController@getProfile');
        Route::get('get_balance_logs', 'OrderController@getBalanceLogs');
        Route::get('get_reserve_logs', 'OrderController@getReserveLogs');
    });

    Route::group(['prefix' => 'field'], function () {
        Route::get('get_fields', 'FieldController@getFields');
    });

    Route::group(['prefix' => 'order', 'middleware' => 'auth:api'], function () {
        Route::post('reserve', 'OrderController@reserve');
        Route::post('recharge', 'OrderController@recharge');
        Route::get('get_order_detail', 'OrderController@getOrderDetail');

    });

    Route::group(['prefix' => 'payment', 'middleware' => 'auth:api'], function () {
        Route::post('payment_by_balance', 'PaymentController@paymentByBalance');
        Route::post('payment_by_wechat', 'PaymentController@paymentByWechat');
    });
    Route::get('payment/get_wechat_jssdk_config', 'PaymentController@getWechatJssdkConfig');

    Route::any('payment/wechat_pay_notify', 'PaymentController@wechatPayNotify');//微信支付回调

    Route::get('common/get_code', 'CommonController@getCode');//获取验证码
    Route::get('common/check_code', 'CommonController@checkCode');//检测验证码


});
#endregion