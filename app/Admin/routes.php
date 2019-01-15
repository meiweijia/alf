<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('setting/users', 'UserController');
    $router->resource('setting/fields', 'FieldController');
    $router->resource('setting/field_profiles', 'FieldProfileController');
    $router->resource('setting/orders', 'OrderController');
    $router->get('setting/badminton', 'ReserveController@badminton');
    $router->get('setting/basketball', 'ReserveController@basketball');

});
