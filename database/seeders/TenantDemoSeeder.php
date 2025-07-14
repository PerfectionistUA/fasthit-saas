<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantDemoSeeder extends Seeder
{
    // database/seeders/TenantDemoSeeder.php
    public function run(): void
    {
        $owner = \App\Models\User::factory()->create([
            'email' => 'owner@demo.local',
            'email_verified_at' => now(),
        ]);

        $tenant = \App\Models\Tenant::factory()->create([
            'name' => 'Demo Company',
            'domain' => 'demo.local',
        ]);

        // звʼязок owner ↔ tenant
        $tenant->users()->attach($owner->id, ['is_owner' => true]);
        $owner->assignRole('organization-admin', $tenant->id);
    }
}
