<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Rockbuzz\LaraPricing\Models\Plan;

$factory->define(Plan::class, function (Faker $faker) {
    $name = $faker->unique()->word;
    return [
        'name' => $name,
        'slug' => \Illuminate\Support\Str::slug($name),
        'description' => $faker->paragraph,
        'price' => $faker->numberBetween(1990, 5990),
        'interval' => 'month',
        'period' => 1,
        'trial_period_days' => 0,
        'order_column' => 1
    ];
});
