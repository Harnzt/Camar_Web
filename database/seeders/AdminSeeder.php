<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL', 'superadmin@camar.id');
        $password = env('SUPER_ADMIN_PASSWORD', 'Admin123!');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('SUPER_ADMIN_NAME', 'Super Admin CAMAR'),
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'account_category' => 'personal',
                'status' => 'verified',
                'verified_at' => now(),
            ]
        );
    }
}
