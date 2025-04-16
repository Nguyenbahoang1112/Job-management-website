<?php

namespace Database\Seeders;

use App\Models\TaskGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\User;
class TaskGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $userIds = User::pluck('id');

        for($i = 0;$i<100;$i++){
            TaskGroup::create([
                'name' => $faker->name,
                'is_admin_created' => $faker->numberBetween(0,1),
                'user_id' => $userIds->random(),
            ]);
        }
    }
}
