<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantUserSeeder extends Seeder
{
    public function run(): void
    {
        // Беремо всіх тенантів
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            // Формуємо email на основі домену, наприклад: user@global-federation.org
            $email = 'user@'.$tenant->domain;

            /** @var User $user */
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $tenant->name.' User',
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'current_tenant_id' => $tenant->id,
                ]
            );

            // Додаємо звʼязок у pivot; is_owner = false
            $user->tenants()
                ->syncWithoutDetaching([
                    $tenant->id => ['is_owner' => false],
                ]);
        }
    }
}
