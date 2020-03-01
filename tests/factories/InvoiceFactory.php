<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Rockbuzz\LaraPricing\Models\{Invoice, Subscription};

$factory->define(Invoice::class, function (Faker $faker) {
    return [
        'price' => $faker->numberBetween(1990, 3990),
        'due_date' => $faker->dateTimeBetween()->format('Y-m-d'),
        'subscription_id' => factory(Subscription::class)->create()
    ];
});
