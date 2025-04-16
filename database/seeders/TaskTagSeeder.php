<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\Tag;
use App\Models\Task;
use App\Models\TaskTag;

class TaskTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $tagIds = Tag::pluck('id');
        $taskIds = Task::pluck('id');

        for($i = 0;$i<100;$i++){
            TaskTag::create([
                'task_id' => $taskIds->random(),
                'tag_id' => $tagIds->random(),
            ]);
        }
    }
}
