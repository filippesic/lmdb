<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);


        DB::table('genres')->insert([
            ['name' => 'Sci-Fi', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Drama', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Mystery', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Horror', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Comedy', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Sports', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Biography', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Fantasy', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Thriller', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        DB::table('video_types')->insert([
            ['type' => 'Movie', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['type' => 'TV-Show', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        DB::table('artist_types')->insert([
            ['type' => 'Actor', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['type' => 'Director', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
        ]);

        DB::table('roles')->insert([
            ['name' => 'user', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'administrator', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
        ]);

        $this->call(UserSeeder::class); // Creates Users
        $this->call(ArtistSeeder::class); // Creates actors and directors
        $this->call(VideoSeeder::class); // Creates only for 'TV Shows 20x'
        $this->call(SeasonSeeder::class); // Creates seasons only for 'TV shows'

        factory(\App\Video::class, 50)->create([ // Creates only for 'Movies'
            'video_type_id' => 1
        ]);

        $videos = \App\Video::all()->random()->get();
        $users = \App\User::with('rated')->get();

        foreach ($videos as $video) {
            for($i = 0; $i < 3; $i++) {
                $video->genres()->attach(\App\Genre::all()->unique()->random()->id); // Populating pivot table for video genres
            }
            for($i = 0; $i < 5; $i++) {
                $video->artists()->attach(\App\Artist::all()->unique()->random()->id); // Populating pivot table for video artists
            }

            foreach ($users->random(10) as $user) {
                $user->rated()->attach($video->id, ['rate' => rand(1, 10)]); // Populating pivot table for video artists
               // \Illuminate\Support\Facades\DB::table('rates')->insert([]);
            }
        }
    }
}
