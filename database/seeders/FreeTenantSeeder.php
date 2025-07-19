<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FreeTenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::updateOrCreate(
            ['id' => config('tenant.free_tenant_id')],
            [
                'name' => 'Free Organization',
                'domain' => 'free-org.org',
                'status' => 'active',
                'timezone' => 'Europe/Kyiv',
                'locale' => 'uk',
                'created_by' => null,
                'updated_by' => null,
                'uuid' => (string) Str::uuid(),
            ]
        );
        // ← Після ручної вставки id=7, синхронізуємо sequence, щоб nextval() віддавав 8 і більше
        DB::statement(<<<'SQL'
            SELECT setval(
              pg_get_serial_sequence('tenants','id'),
              (SELECT MAX(id) FROM tenants)
            )
        SQL);
    }
}
