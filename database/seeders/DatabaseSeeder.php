<?php

namespace Database\Seeders;

use App\Models\User;
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
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Admin@123'),
                'role' => 1
            ]
        );
        User::updateOrCreate(
            ['email' => 'test@gmail.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('Test@123'),
                'role' => 0
            ]
        );
        User::updateOrCreate(
            ['email' => 'hoang@gmail.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('Hoang@123'),
                'role' => 0
            ]
        );
    }
}
