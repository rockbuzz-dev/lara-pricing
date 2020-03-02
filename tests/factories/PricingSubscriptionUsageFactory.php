<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Rockbuzz\LaraPricing\Models\{PricingSubscriptionUsage, PricingSubscription, PricingFeature};

$factory->define(PricingSubscriptionUsage::class, function (Faker $faker) {
    return [
        'used' => $faker->numberBetween(1, 3),
        'subscription_id' => factory(PricingSubscription::class)->create(),
        'feature_id' => factory(PricingFeature::class)->create(),
        'metadata' => null
    ];
});
