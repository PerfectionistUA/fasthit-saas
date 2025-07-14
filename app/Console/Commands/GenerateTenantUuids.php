<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateTenantUuids extends Command
{
    protected $signature = 'tenants:generate-uuids';

    protected $description = 'Generates UUIDs for existing tenants that do not have one.';

    public function handle(): int
    {
        $tenants = Tenant::whereNull('uuid')->get();
        // Перевіряємо, чи є орендарі без UUID
        if ($tenants->isEmpty()) {
            $this->info('No tenants without UUIDs found. All good!');

            return self::SUCCESS;
        }

        $this->info(sprintf('Generating UUIDs for %d tenants...', $tenants->count()));

        foreach ($tenants as $tenant) {
            $tenant->uuid = (string) Str::uuid();
            // Використовуємо saveQuietly, щоб уникнути Observer-ів та оновлення timestamps
            $tenant->saveQuietly();
            $this->info(sprintf('Generated UUID %s for tenant "%s" (ID: %d)', $tenant->uuid, $tenant->name, $tenant->id));
        }

        $this->info('UUID generation complete.');

        return self::SUCCESS;
    }
}
