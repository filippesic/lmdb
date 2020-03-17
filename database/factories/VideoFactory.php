<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Artist;
use App\Season;
use App\Video;
use Faker\Generator as Faker;

$factory->define(Video::class, function (Faker $faker) {
    //$videoType = $faker->randomElement([1, 2, 3]);
    //$videoType = $faker->numberBetween($min = 2, $max = 3);
    //$filePath = storage_path('app/public/videos');
//    if($videoType == 1) {
//        $season = $faker->numberBetween($min = 1, $max = 17);
//    } elseif ($videoType == 3) {
//        $season = $faker->numberBetween($min = 1, $max = 4);
//    } else {
//        $season = null;
//    }
    $director = Artist::all()->where('artist_type_id', 2)->random()->id;

    return [
        'video_type_id' => 2, // TV Show
        'name' => $faker->unique()->realText($maxNubChars = 30),
        'poster' =>  'https://i.picsum.photos/id/' . rand(1, 500) . '/200/300.jpg', // FIND A BETTER SOLUTION MATE
        'trailer' =>  'https://www.youtube.com/embed/vKQi3bBA1y8',
        'mpaa_rating' => $faker->randomElement(['PG-13', 'R', 'NR', 'G', 'PG', 'A']),
        'duration_in_minutes' => $faker->numberBetween($min = 55, $max = 240),
        'release_date' =>$faker->date(),
        'country' => $faker->country,
        'plot' => $faker->sentence($nbWords = 40),
        'director_id' => $director,
    ];
});
