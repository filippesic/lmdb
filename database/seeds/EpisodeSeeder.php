<?php

use App\Video;
use Illuminate\Database\Seeder;

class EpisodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Episode::class, 50)->make()->each(function ($episode) {
            $episode->season()->associate(Video::all()->random()->id);
            $episode->save();
        });
    }
}
