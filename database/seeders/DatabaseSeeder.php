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
        NoteSeeder::class,
        TeamSeeder::class,
        TaskGroupSeeder::class,
        TaskSeeder::class,
        RepeatTaskSeeder::class,
        SearchHistorySeeder::class,
        TagSeeder::class,
      
        TaskTagSeeder::class,
        TeamUserSeeder::class,

      ]);

    }
}
