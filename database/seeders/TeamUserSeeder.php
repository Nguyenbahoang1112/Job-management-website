<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\User;
use App\Models\Team;
use App\Models\TeamUser;

class TeamUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $userIds = User::pluck('id');
        $teamIds = Team::pluck('id');
        for($i = 0;$i<150;$i++){
            TeamUser::create([
                'user_id' => $userIds->random(),
                'team_id' => $teamIds->random()
            ]);
        }
    }
}
