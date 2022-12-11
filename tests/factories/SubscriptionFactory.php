<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Tests\Models\Account;
use Faker\Generator as Faker;
use Rockbuzz\LaraPricing\Models\{Subscription, Plan};

$factory->define(Subscription::class, function (Faker $faker) {
    $startAt = $faker->dateTimeBetween();
    return [
        'start_at' => $startAt,
        'finish_at' => null,
        'canceled_at' => null,
        'due_day' => $startAt->format('d'),
        'subscribable_id' => factory(Account::class)->create(),
        'subscribable_type' => Account::class,
        'plan_id' => factory(Plan::class)->create()
    ];
});
