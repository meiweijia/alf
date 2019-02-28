<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'ReserveController@badminton');
    $router->resource('setting/users', 'UserController');
    $router->post('setting/users/recharge','UserController@recharge')->name('users.recharge');
    $router->resource('setting/fields', 'FieldController');
    $router->resource('setting/field_profiles', 'FieldProfileController');
    $router->resource('setting/orders', 'OrderController');
    $router->resource('setting/wechat_menus', 'WechatMenuController');

    $router->get('setting/badminton', 'ReserveController@badminton');
    $router->get('setting/basketball', 'ReserveController@basketball');
});
