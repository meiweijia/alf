<?php

use Faker\Generator as Faker;

$factory->define(App\Models\UserProfile::class, function (Faker $faker) {
    return [
        'level' => $faker->numberBetween(1, 4),
        'balance' => $faker->randomFloat(2, 0, 10000)
    ];
});
