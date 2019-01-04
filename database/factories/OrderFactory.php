<?php

use App\Models\Order;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Order::class, function (Faker $faker) {
    // 随机取一个用户
    $user = User::query()->inRandomOrder()->first();
    // 随机生成订单状态
    $status = $faker->randomElement(array_keys(Order::$orderStatusMap));

    return [
        'total_fees' => 0,
        'remark' => $faker->sentence,
        'paid_at' => $faker->dateTimeBetween('-30 days'), // 30天前到现在任意时间点
        'payment_method' => $faker->randomElement(['wechat', 'alipay', 'balance']),
        'payment_no' => $faker->uuid,
        'status' => $status,
        'user_id' => $user->id,
    ];
});
