<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Rockbuzz\LaraPricing\Models\{PricingActivity, PricingSubscriptionUsage};

$factory->define(PricingActivity::class, function (Faker $faker) {
    $activityable = factory(PricingSubscriptionUsage::class)->create();
    $causeable = factory(Tests\Models\User::class)->create();
    return [
        'description' => $faker->paragraph,
        'changes' => null,
        'activityable_id' => $activityable->id,
        'activityable_type' => PricingSubscriptionUsage::class,
        'causeable_id' => $causeable->id,
        'causeable_type' => Tests\Models\User::class,
    ];
});
