<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SEED_SUPER_ADMIN_EMAIL', 'admin@example.com');
        $password = env('SEED_SUPER_ADMIN_PASSWORD', 'password');

        /** @var User $user */
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );

        if (! $user->hasRole('super-admin')) {
            $user->assignRole('super-admin');
        }

        $this->command->info("Super-admin credentials  →  {$email} / {$password}");
    }
}
