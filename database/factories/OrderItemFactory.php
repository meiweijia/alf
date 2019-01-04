<?php

use App\Models\OrderItem;
use Faker\Generator as Faker;

$factory->define(OrderItem::class, function (Faker $faker) {

    // 从数据库随机取一个场地
    $field = \App\Models\FieldProfile::query()->where('amount', '>', 0)->inRandomOrder()->first();
    // 把场地数量设为0
    $field->update(['amount' => 0]);

    return [
        'field_profile_id' => $field->id,
        'amount' => 1,
        'fees' => $field->fees,
    ];
});
