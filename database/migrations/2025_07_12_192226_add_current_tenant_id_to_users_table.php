<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Використовуємо foreignId для прив'язки до tenants.id
            // Nullable, бо користувач може не мати вибраного тенанта або не належати жодному.
            $table->foreignId('current_tenant_id')
                ->nullable()
                ->constrained('tenants')
                ->nullOnDelete(); // Якщо тенант видаляється, скидаємо current_tenant_id
            $table->index('current_tenant_id'); // Додаємо індекс
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_tenant_id']);
            $table->dropIndex(['current_tenant_id']);
            $table->dropColumn('current_tenant_id');
        });
    }
};
