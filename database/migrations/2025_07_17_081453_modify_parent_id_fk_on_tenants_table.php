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
        Schema::table('tenants', function (Blueprint $table) {
            // спочатку видаляємо старий FK
            $table->dropForeign(['parent_id']);
            // додаємо новий з ON DELETE SET NULL
            $table->foreign('parent_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->foreign('parent_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('restrict');
        });
    }
};
