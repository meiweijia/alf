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
    $arr = [
        [
            'no' => 1,
            'name' => '场地1',
            'info' => [
                'time' => '8',
                'price' => 50
            ]
        ],
        [
            'no' => 2,
            'name' => '场地2',
            'info' => [
                'time' => '8',
                'price' => 50
            ]
        ],
        [
            'no' => 3,
            'name' => '场地3',
            'info' => [
                'time' => '8',
                'price' => 50
            ]
        ],
    ];
    return \App\Model\UserProfile::all();
    return json_encode($arr);
});