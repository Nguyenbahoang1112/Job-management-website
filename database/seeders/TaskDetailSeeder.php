<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\TaskDetail;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Faker\Factory as Faker;

class TaskDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $tasks = Task::pluck('id');
        $assignToUser = (bool)random_int(0, 1);
        for($i = 0;$i<100;$i++){
            TaskDetail::create([
                'task_id' => $tasks->random(),
                'title' => $faker->title,
                'description' => $faker->paragraph,
                'due_date' => $faker->dateTimeBetween('now', '+30 days'),
                'time' =>  $faker->time('H:i:s'),
                'priority' => $faker->numberBetween(0,1),
                'status' => rand(0, 2),
                'parent_id' => null,
            ]);

        }
    }
}
