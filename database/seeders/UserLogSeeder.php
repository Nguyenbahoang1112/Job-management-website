<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserLog;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Faker\Factory as Faker;

class UserLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $userIds = User::pluck('id');

        for($i= 0;$i<100;$i++){
            $login = $faker->dateTimeBetween('-1 month', 'now');
            $logout = (clone $login)->modify('+' . rand(5, 180) . ' minutes');
            UserLog::create([
                'user_id'=>$userIds->random(),
                'login_time'=>$login,
                'logout_time'=>$logout,
                'ip_address' => $faker->ipv4
            ]);
        }
    }
}
