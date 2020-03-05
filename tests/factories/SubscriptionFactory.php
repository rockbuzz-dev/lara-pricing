<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Tests\Models\Workspace;
use Faker\Generator as Faker;
use Rockbuzz\LaraPricing\Models\{Subscription, Plan};

$factory->define(Subscription::class, function (Faker $faker) {
    $name = $faker->unique()->word;
    $workspace = factory(Workspace::class)->create();
    $startAt = $faker->dateTimeBetween();
    return [
        'name' => $name,
        'slug' => \Illuminate\Support\Str::slug($name),
        'start_at' => $startAt,
        'finish_at' => null,
        'canceled_at' => null,
        'due_day' => $startAt->format('d'),
        'subscribable_id' => $workspace->id,
        'subscribable_type' => Workspace::class,
        'plan_id' => factory(Plan::class)->create()
    ];
});
