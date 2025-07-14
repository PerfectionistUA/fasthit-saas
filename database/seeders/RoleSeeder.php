<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // глобальна роль
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

        // шаблонні ролі для тенантів (tenant_id == null тут НЕ ставимо)
        foreach (['organization-admin', 'organization-member'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
