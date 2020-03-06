<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Season;
use App\Video;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\DB;



$factory->define(Season::class, function (Faker $faker) {

    return [
        $vd = 'video_id' => Video::where('video_type_id', 2)->get()->random()->id,
        'season_number' => $faker->numberBetween(1, Video::where('id', $vd)->get()->random()->seasons()->count())
    ];
});
