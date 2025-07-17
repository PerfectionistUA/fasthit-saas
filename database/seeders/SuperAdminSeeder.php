<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

use function globalTeamId; // helper

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Отримуємо email та пароль супер-адміна з env-файлу
        //    або використовуємо значення за замовчуванням
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

        /* ───── Увімкнули «глобальний tenant» ───── */
        app(PermissionRegistrar::class)
            ->setPermissionsTeamId(globalTeamId());   // helper із app/Support/helpers.php

        /* ───── Призначили роль, якщо ще немає ───── */
        if (! $user->hasRole('super-admin')) {
            $user->assignRole('super-admin');         // у pivot буде tenant_id = 0
        }

        /* ───── Friendly output ───── */
        $this->command->info("✅ Super-admin seeded: {$email}");
    }
}
