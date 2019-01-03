<?php

use Faker\Generator as Faker;

$factory->define(App\Models\FieldProfile::class, function (Faker $faker) {
    return [
        'fees' => $faker->randomFloat(2, 0.00, 100.00),
        'amount' => 1,
    ];
});
