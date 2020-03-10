<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Artist;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Storage;

$factory->define(Artist::class, function (Faker $faker) {
    return [
        'artist_type_id' => $faker->randomElement([1, 2]),
        'poster' => 'artist_id_' . rand(1, 9) . '.jpg',
        'name' => $faker->name,
        'surname' => $faker->lastName,
        'bio' => $faker->paragraphs($nb = 6, $asText = true),
        'gender' => $faker->randomElement(['male', 'female']),
        'birth_date' => $faker->date($format = 'Y-m-d'),
        'country' => $faker->country
    ];
});
