<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class PostsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws Exception
     */
    public function run()
    {
        //
        $faker = Faker::create();
        $titles = [
            "Hello, World!",
            "Two's company",
            "Three's a crowd",
            "May the fourth be with you",
            "Forfar 5 East Fife 4",
            "Six and Dregs and Rick and Roll"
        ];
        for ($i=0; $i<count($titles); $i++) {
            DB::table('posts')->insert([
                'title' => $titles[$i],
                'body' => $faker->sentences($nb = random_int(4, 6), $asText = true),
                'user_id' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ]);
        }
    }
}
