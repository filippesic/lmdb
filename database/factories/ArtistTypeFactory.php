<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\ArtistType;
use Faker\Generator as Faker;

$factory->define(ArtistType::class, function (Faker $faker) {
    return [
        'type' => $faker->randomElement(['actor', 'director'])
    ];
});
