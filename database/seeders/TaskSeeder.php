<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tag;
use App\Models\Team;
use Faker\Factory as Faker;
class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $userIds = User::pluck('id');
        $teamIds = Team::pluck('id');
        $assignToUser = (bool)random_int(0, 1);
        for($i = 0;$i<100;$i++){
            Tag::create([
                'title' => $faker->title,
                'description' => $faker->paragraph,
                'due_date' => $faker->dateTimeBetween('now', '+30 days'),
                'time' =>  $faker->time('H:i:s'),
                'priority' => $faker->numberBetween(0,1),
                'status' => rand(1,3),
                'is_admin_created'=> rand(0,1),
                'user_id' => $assignToUser ? fake()->randomElement($userIds) : null,
                'team_id' => !$assignToUser ? fake()->randomElement($teamIds) : null,
            ]);

        }
    }
}
