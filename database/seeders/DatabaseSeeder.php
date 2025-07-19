<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Спочатку права/ролі
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        // 2) Seed “free”-тенант з id = 7
        $this->call(FreeTenantSeeder::class);

        // 3) Тепер створюємо Super Admin (user_id = 1), default current_tenant_id = 7 вже прив’язується без помилок
        $this->call(SuperAdminSeeder::class);

        // 4) Локальні/demo дані
        if (app()->environment(['local', 'staging'])) {
            $this->call([
                TenantDemoSeeder::class,
                TenantUserSeeder::class,
            ]);
        }
    }
}
