<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\RepeatRule;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RepeatRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $tasks = Task::pluck('id');

        for($i = 0;$i<100;$i++){
            RepeatRule::create([
                'task_id' => $tasks->random(),
                'repeat_type' => $faker->randomElement([1, 2, 3]),
                'repeat_interval' => null,
                'repeat_due_date' => $faker->dateTimeBetween('+10 days', '+30 days'),
                'status_repeat_task' => rand(0, 2),
                'priority_repeat_task' => $faker->numberBetween(0, 1),
            ]);
        }
    }
}
