<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Rockbuzz\LaraPricing\Models\Feature;

$factory->define(Feature::class, function (Faker $faker) {
    $name = $faker->unique()->word;
    return [
        'name' => $name,
        'slug' => \Str::slug($name),
        'sort_order' => array_rand([1,5,10])
    ];
});
