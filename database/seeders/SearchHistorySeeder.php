<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\User;
use App\Models\SearchHistory;
class SearchHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $userIds = User::pluck('id');
        for($i = 0;$i< 100;$i++){
            SearchHistory::create([
                'search_query' => $faker->text,
                'user_id' => $userIds->random(),
            ]);
        }
    }
}
