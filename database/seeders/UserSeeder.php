<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => '12345678',
                'role' => 'Admin',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'User',
                'email' => 'user@example.com',
                'password' => '12345678',
                'role' => 'User',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($data as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make($user['password']),
                'role' => $user['role'],
                'email_verified_at' => $user['email_verified_at'],
            ]);
        }
    }
}
