<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PostsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $faker = Faker::create();
        $titles = ["Hello, World!", "Two's company", "Three's a crowd", "May the fourth be wth you", "Forfar 5 East Fife 4"];
        for($i=0;$i<count($titles);$i++) {
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
