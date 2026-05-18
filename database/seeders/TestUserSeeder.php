<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    /**
     * Seed test users for elearning flow testing.
     */
    public function run(): void
    {
        $testUsers = [
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
                'role' => 'user',
            ],
            [
                'name' => 'Siswa Demo',
                'email' => 'siswa@example.com',
                'password' => 'password',
                'role' => 'user',
            ],
        ];

        foreach ($testUsers as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => bcrypt($userData['password']),
                    'role' => $userData['role'],
                ]
            );
        }
    }
}