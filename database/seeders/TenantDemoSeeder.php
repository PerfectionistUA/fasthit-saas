<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantDemoSeeder extends Seeder
{
    // database/seeders/TenantDemoSeeder.php
    public function run(): void
    {
        // Забезпечуємо, що запис існує і оновлюємо за потреби
        // 1. Demo-owner (ідемпотентно)
        $owner = User::updateOrCreate(
            ['email' => 'owner@demo.local'],
            [
                'name' => 'Demo Owner',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Demo-tenant (ідемпотентно)
        $tenant = Tenant::updateOrCreate(
            ['domain' => 'demo.local'],               // ← ключ для «унікальності»
            [
                'name' => 'Demo Company',
                'uuid' => (string) Str::uuid(),
            ]
        );

        // 3. Прив’язуємо власника до тенанта, якщо ще не прив’язаний
        $tenant->users()->syncWithoutDetaching([
            $owner->id => ['is_owner' => true],
        ]);
    }
}
