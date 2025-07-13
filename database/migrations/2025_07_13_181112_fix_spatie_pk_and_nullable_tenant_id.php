<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ----- model_has_roles -----
        DB::statement('ALTER TABLE model_has_roles DROP CONSTRAINT IF EXISTS model_has_roles_pkey');
        DB::statement('ALTER TABLE model_has_roles '
            .'ALTER COLUMN tenant_id DROP NOT NULL');
        DB::statement('ALTER TABLE model_has_roles '
            .'ADD PRIMARY KEY (role_id, model_type, model_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS model_has_roles_tenant_idx '
            .'ON model_has_roles (tenant_id)');

        // ----- model_has_permissions -----
        DB::statement('ALTER TABLE model_has_permissions DROP CONSTRAINT IF EXISTS model_has_permissions_pkey');
        DB::statement('ALTER TABLE model_has_permissions '
            .'ALTER COLUMN tenant_id DROP NOT NULL');
        DB::statement('ALTER TABLE model_has_permissions '
            .'ADD PRIMARY KEY (permission_id, model_type, model_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS model_has_permissions_tenant_idx '
            .'ON model_has_permissions (tenant_id)');
    }

    public function down(): void
    {
        // повернути «tenant_id NOT NULL + у PK» (опціонально)
        DB::statement('ALTER TABLE model_has_roles DROP CONSTRAINT model_has_roles_pkey');
        DB::statement('ALTER TABLE model_has_roles ALTER COLUMN tenant_id SET NOT NULL');
        DB::statement('ALTER TABLE model_has_roles '
            .'ADD PRIMARY KEY (role_id, model_type, model_id, tenant_id)');
        DB::statement('DROP INDEX IF EXISTS model_has_roles_tenant_idx');

        DB::statement('ALTER TABLE model_has_permissions DROP CONSTRAINT model_has_permissions_pkey');
        DB::statement('ALTER TABLE model_has_permissions ALTER COLUMN tenant_id SET NOT NULL');
        DB::statement('ALTER TABLE model_has_permissions '
            .'ADD PRIMARY KEY (permission_id, model_type, model_id, tenant_id)');
        DB::statement('DROP INDEX IF EXISTS model_has_permissions_tenant_idx');
    }
};
