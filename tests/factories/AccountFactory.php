<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;

$factory->define(\Tests\Models\Account::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->word
    ];
});
