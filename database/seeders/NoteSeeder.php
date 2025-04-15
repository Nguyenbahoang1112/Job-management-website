<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\Note;
use App\Models\User;
class NoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $userIds = User::pluck('id');
        for($i = 0;$i< 100;$i++){
            Note::create([
                'content' => $faker->paragraph,
                'user_id' => $userIds->random(),
            ]);
        }
    }
}
