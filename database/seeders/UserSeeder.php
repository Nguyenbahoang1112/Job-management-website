<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for($i = 0 ;$i< 40;$i++){
            User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => encrypt('123456'),
                'status'=> $faker->numberBetween(0,1),
                'role' => 0
            ]);
        }
       
    }
}
