<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserLog;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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
                'email' => $faker->unique()->safeEmail,
                'password' => bcrypt('Hoang@123'),
                'status'=> $faker->numberBetween(0,1),
                'role' => 0
            ]);
        }

        $faker = Faker::create();
        $userIds = User::pluck('id');

        for($i= 0;$i<100;$i++){
            $login = $faker->dateTimeBetween('-1 month', 'now');
            $logout = (clone $login)->modify('+' . rand(5, 180) . ' minutes');
            UserLog::create([
                'user_id'=>$userIds->random(),
                'login_time'=>$login,
                'logout_time'=>$logout,
            ]);
        }
    }
}
