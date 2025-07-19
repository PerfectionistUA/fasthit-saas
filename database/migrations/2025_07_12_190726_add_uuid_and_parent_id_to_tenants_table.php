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
        Schema::table('tenants', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->nullable()->after('id');
            $table->foreignId('parent_id')->nullable()
                ->constrained('tenants')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /*Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });*/
        // якщо в нас додавали і uuid, і parent_id → видаляємо обидва
        Schema::table('tenants', function (Blueprint $table) {
            // Constraint на parent_id
            DB::statement('ALTER TABLE tenants DROP CONSTRAINT IF EXISTS tenants_parent_id_foreign');
            if (Schema::hasColumn('tenants', 'parent_id')) {
                $table->dropColumn('parent_id');
            }
            if (Schema::hasColumn('tenants', 'uuid')) {
                $table->dropColumn('uuid');
            }
        });
    }
};
