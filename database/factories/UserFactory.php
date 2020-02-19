<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use Faker\Generator as Faker;

$factory->define(User::class, function (Faker $faker) {
    return [
        'role_id' => 1,
        'name' => $faker->name,
        'surname' => $faker->lastName,
        'email' => $faker->email,
        'password' => bcrypt('321')
    ];
});
