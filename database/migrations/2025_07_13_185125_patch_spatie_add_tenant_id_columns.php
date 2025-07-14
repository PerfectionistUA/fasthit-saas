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
        /* ---------- 1. roles.tenant_id ---------------------------------- */
        if (! Schema::hasColumn($this->rolesTable, 'tenant_id')) {
            Schema::table($this->rolesTable, function (Blueprint $t) {
                $t->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $t->index('tenant_id');
            });
        }

        /* ---------- 2. pivot-таблиці ------------------------------------ */
        foreach ($this->pivotTables as $table => $_) {

            // a) якщо є team_id — перейменовуємо
            if (Schema::hasColumn($table, 'team_id')
                && ! Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, fn (Blueprint $t) => $t->renameColumn('team_id', 'tenant_id'));
            }

            // b) якщо зовсім нема — додаємо
            if (! Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('tenant_id')
                        ->nullable()
                        ->after('model_id');
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
