<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Завжди накочуємо базові сидери прав і ролей:
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        // 2) У локальному (і/або staging) середовищі — лише для розробки:
        if (app()->isLocal()) {
            // Створюємо/оновлюємо тестового супер-адміна
            $this->call(SuperAdminSeeder::class);

            // Створюємо демо-тенант + його «owner»
            $this->call(TenantDemoSeeder::class);
        }
    }
}
