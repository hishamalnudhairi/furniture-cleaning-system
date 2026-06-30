<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // مدير تجريبي
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'مدير النظام',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'phone' => '0500000000',
                'is_active' => true,
            ]
        );

        // موظف (عامل) تجريبي
        User::updateOrCreate(
            ['email' => 'worker@example.com'],
            [
                'name' => 'موظف تجريبي',
                'password' => Hash::make('password'),
                'role' => User::ROLE_WORKER,
                'phone' => '0500000001',
                'is_active' => true,
            ]
        );
    }
}
