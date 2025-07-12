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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // назва орендаря (tenant)
            $table->string('domain')->unique(); // unique domain for the tenant

            // Активність / призупинення / закриття
            $table->enum('status', ['active', 'suspended', 'archived'])
                ->default('active');

            // Коли закінчується підписка / trial
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Локалізація
            $table->string('timezone', 64)->default('UTC');
            $table->string('locale', 8)->default('en');

            // Аудит (хто створив / оновив)
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();     // created_at / updated_at
            $table->softDeletes();    // deleted_at для «м’якого» видалення
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
