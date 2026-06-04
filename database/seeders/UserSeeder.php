<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\CtfLogger;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'username' => 'player_alpha',
                'password' => Hash::make('password'),
                'role' => 'player',
            ],
            [
                'username' => 'player_bravo',
                'password' => Hash::make('password'),
                'role' => 'player',
            ],
            [
                'username' => 'player_charlie',
                'password' => Hash::make('password'),
                'role' => 'player',
            ],
        ];

        foreach ($users as $data) {
            User::create($data);
        }

        CtfLogger::info('seeder:UserSeeder', 'Seeded ' . count($users) . ' users.', [
            'usernames' => array_column($users, 'username'),
        ]);
    }
}
