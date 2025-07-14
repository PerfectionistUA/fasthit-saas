<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // таблиці, де потрібна колонка
    protected array $pivotTables = [
        'model_has_roles' => ['role_id'],
        'model_has_permissions' => ['permission_id'],
    ];

    protected string $rolesTable = 'roles';

    public function up(): void
    {
        /* ---------- 1. roles.tenant_id ---------- */
        if (Schema::hasTable($this->rolesTable) &&   // 🆕 guard
            ! Schema::hasColumn($this->rolesTable, 'tenant_id')) {

            Schema::table($this->rolesTable, function (Blueprint $t) {
                $t->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $t->index('tenant_id', 'roles_tenant_idx');
            });
        }

        /* ---------- 2. pivot-таблиці ------------- */
        foreach ($this->pivotTables as $table => $_) {

            if (! Schema::hasTable($table)) {
                // таблиця ще не створена -> пропускаємо
                continue;
            }

            // a) team_id → tenant_id
            if (Schema::hasColumn($table, 'team_id') &&
                ! Schema::hasColumn($table, 'tenant_id')) {

                Schema::table($table,
                    fn (Blueprint $t) => $t->renameColumn('team_id', 'tenant_id'));
            }

            // b) зовсім нема tenant_id
            if (! Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    $t->unsignedBigInteger('tenant_id')->nullable()
                        ->after('model_id');
                    $t->index('tenant_id', $table.'_tenant_idx');
                });
            }
        }
    }

    public function down(): void
    {
        // Не обовʼязково, але для симетрії
        foreach ($this->pivotTables as $table => $_) {
            if (Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, fn (Blueprint $t) => $t->dropColumn('tenant_id'));
            }
        }
        if (Schema::hasColumn($this->rolesTable, 'tenant_id')) {
            Schema::table($this->rolesTable, fn (Blueprint $t) => $t->dropColumn('tenant_id'));
        }
    }
};
