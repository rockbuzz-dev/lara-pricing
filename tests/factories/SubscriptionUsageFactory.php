<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Rockbuzz\LaraPricing\Models\{SubscriptionUsage, Subscription, Feature};

$factory->define(SubscriptionUsage::class, function (Faker $faker) {
    return [
        'used' => $faker->numberBetween(1, 3),
        'subscription_id' => factory(Subscription::class)->create(),
        'feature_id' => factory(Feature::class)->create(),
    ];
});
