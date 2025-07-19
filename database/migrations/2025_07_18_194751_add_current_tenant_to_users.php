<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Додаємо NOT NULL поле з дефолтом на Free Organization (id = 7)
            $table->foreignId('current_tenant_id')
                ->nullable()
                ->default(7)
                ->constrained('tenants')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Композитний індекс (current_tenant_id, id) для швидшого фільтрування та сортування
            $table->index([
                'current_tenant_id', 'id'],
                'users_tenant_id_id_index');
        });
    }

    public function down(): void
    {
        // 1) Видаляємо індекс лише якщо він існує
        DB::statement('DROP INDEX IF EXISTS users_tenant_id_id_index');

        // 2) Видаляємо FK-констрейнт лише якщо він є
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_current_tenant_id_foreign');

        // 3) Видаляємо колонку, але тільки якщо вона є
        if (Schema::hasColumn('users', 'current_tenant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('current_tenant_id');
            });
        }
    }
};
