<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;

$factory->define(\Tests\Models\Workspace::class, function (Faker $faker) {
    $name = $faker->unique()->word;
    return [
        'name' => $name
    ];
});
