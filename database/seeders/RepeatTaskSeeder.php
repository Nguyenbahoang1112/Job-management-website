<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\RepeatTask;
use App\Models\Task;
class RepeatTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $taskIds = Task::pluck('id');

        for($i = 0;$i< 100;$i++){
            RepeatTask::create([
                'task_id' => $taskIds->random(),
                'repeat_type' => rand(1,3),
                'repeat_interval' => 3,
                'repeat_date' => $faker->dateTimeBetween('now', '+30 days'),
                
            ]);

        }
    }
}
