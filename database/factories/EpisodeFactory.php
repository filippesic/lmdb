<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Episode;
use App\Season;
use Faker\Generator as Faker;

$factory->define(Episode::class, function (Faker $faker) {
    return [
        'season_id' => Season::all()->random()->id,
        'name' => $faker->sentence(5, true)
    ];
});
