<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Season;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\DB;



$factory->define(Season::class, function (Faker $faker) {

    return [
        'video_id' => \App\Video::all()->random()->id,
        'episodes' => $faker->numberBetween($min = 11, $max = 27)
    ];
});
