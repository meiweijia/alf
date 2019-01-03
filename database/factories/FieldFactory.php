<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Field::class, function (Faker $faker) {
    static $number = 1;
    return [
        'no' => $number,
        'name' => '场地' . $number++,
        'type' => $faker->randomElement(array_keys(\App\Models\Field::$typeMap))
    ];
});
