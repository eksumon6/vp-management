<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $seedUsers = [
            [
                'name' => 'ডেভেলপার (সুপার এডমিন)',
                'email' => 'developer@example.com',
                'role' => User::ROLE_DEVELOPER,
            ],
            [
                'name' => 'সহকারী কমিশনার (ভূমি)',
                'email' => 'acl@example.com',
                'role' => User::ROLE_ASSISTANT_COMMISSIONER,
            ],
            [
                'name' => 'উপজেলা নির্বাহী অফিসার',
                'email' => 'uno@example.com',
                'role' => User::ROLE_UNO,
            ],
            [
                'name' => 'অফিস সহকারী',
                'email' => 'assistant@example.com',
                'role' => User::ROLE_OFFICE_ASSISTANT,
            ],
        ];

        foreach ($seedUsers as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}
