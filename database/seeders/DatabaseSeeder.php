<?php

namespace Database\Seeders;

use App\Models\RepeateTask;
use App\Models\User;
use App\Models\UserLog;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            // AdminSeeder::class,
            TeamSeeder::class,
            TeamUserSeeder::class,
            TaskGroupSeeder::class,
            TaskSeeder::class,
            TagSeeder::class,
            TaskTagSeeder::class,
            NoteSeeder::class,
            UserLogSeeder::class,
            SearchHistorySeeder::class,

            // RepeatRuleSeeder::class,

            TaskDetailSeeder::class,
      ]);

    }
}
