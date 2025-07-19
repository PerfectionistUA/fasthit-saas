<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Додаємо tenant_id. Nullable, бо можуть бути глобальні токени (наприклад, для Super Admin)
            $table->foreignId('tenant_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete(); // Якщо тенант видаляється, скидаємо tenant_id у токені
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /*Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });*/
        // якщо індекс зветься явно — видаляємо лише якщо є
        DB::statement('DROP INDEX IF EXISTS personal_access_tokens_tenant_id_index');
        DB::statement('ALTER TABLE personal_access_tokens DROP CONSTRAINT IF EXISTS personal_access_tokens_tenant_id_foreign');

        // стовпець теж лише якщо він існує
        if (Schema::hasColumn('personal_access_tokens', 'tenant_id')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }
    }
};
