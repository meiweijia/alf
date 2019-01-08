<?php

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

Route::get('', function () {
    return 404;
    // foreach ($data as $datum) {
    //     var_dump($datum->items->isEmpty());
    // }
    // $app = app('wechat.payment');
    //
    // $config = $app->jssdk->sdkConfig('wx05182447425520f19c721c7a2421721826');
    // $sign = $app->jssdk->buildConfig([
    //     "chooseWXPay" //微信支付
    // ],true);
    // print_r($sign);
    // return view('welcome', compact('config'));
    // return \App\Libraries\Wechat::authUser();
});