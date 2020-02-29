<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Tests\Models\Workspace;
use Faker\Generator as Faker;
use Rockbuzz\LaraPricing\Models\{Subscription, Plan};

$factory->define(Subscription::class, function (Faker $faker) {
    $workspace = factory(Workspace::class)->create();
    return [
        'name' => $faker->unique()->word,
        'start_at' => $faker->dateTimeBetween(),
        'finish_at' => null,
        'canceled_at' => null,
        'subscribable_id' => $workspace->id,
        'subscribable_type' => Workspace::class,
        'plan_id' => factory(Plan::class)->create()
    ];
});
