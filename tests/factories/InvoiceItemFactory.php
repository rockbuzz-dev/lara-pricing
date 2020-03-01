<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Rockbuzz\LaraPricing\Models\{Invoice, InvoiceItem};

$factory->define(InvoiceItem::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'description' => $faker->paragraph,
        'unit_price' => $faker->numberBetween(1990, 3990),
        'quantity' => $faker->numberBetween(1, 3),
        'invoice_id' => factory(Invoice::class)->create()
    ];
});
