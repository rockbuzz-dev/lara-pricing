<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Rockbuzz\LaraPricing\Models\PricingPlan;

$factory->define(PricingPlan::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->word,
        'description' => $faker->paragraph,
        'price' => $faker->numberBetween(1990, 5990),
        'interval' => 'month',
        'period' => 1,
        'trial_period_days' => 0,
        'sort_order' => 1
    ];
});
