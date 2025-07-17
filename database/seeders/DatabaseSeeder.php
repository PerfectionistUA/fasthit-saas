<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Завжди накочуємо базові сидери прав і ролей
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        // 2) Супер-адмін — і для dev, і для тестів
        $this->call(SuperAdminSeeder::class);

        // 3) Демо-тенант + Demo Owner — тільки у локальному або staging
        //    (щоб у PHPUnit-тестах не створювати зайвих записів)
        if (app()->environment(['local', 'staging'])) {
            $this->call(TenantDemoSeeder::class);
        }
    }
}
