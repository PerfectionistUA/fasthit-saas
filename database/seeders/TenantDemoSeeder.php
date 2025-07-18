<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TenantDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Юзер-супер-адмін (має id = 1)
        $owner = User::find(1);

        // Масив даних тенантів
        $tenants = [
            ['domain' => 'global-federation.org', 'name' => 'Global Federation', 'locale' => 'en'],
            ['domain' => 'national-federation.org', 'name' => 'National Federation', 'locale' => 'uk'],
            ['domain' => 'local-federation.org', 'name' => 'Local Federation', 'locale' => 'uk'],
            ['domain' => 'sports-club.org', 'name' => 'Sport Club', 'locale' => 'uk'],
            ['domain' => 'sports-facility.org', 'name' => 'Sports Facility', 'locale' => 'uk'],
            ['domain' => 'com-org.com', 'name' => 'Commercial Organization', 'locale' => 'uk'],
            ['domain' => 'free-org.org', 'name' => 'Free Organization', 'locale' => 'uk'],
            ['domain' => 'free-user.fasthit.com.ua', 'name' => 'Free User', 'locale' => 'uk'],
            ['domain' => 'subscriber.fasthit.com.ua', 'name' => 'Subscriber', 'locale' => 'uk'],
            ['domain' => 'pro-user.fasthit.com.ua', 'name' => 'Pro User', 'locale' => 'uk'],
        ];

        // Списки доменів «безкоштовних» тенантів
        $freeDomains = [
            'free-org.org',
            'free-user.fasthit.com.ua',
        ];

        foreach ($tenants as $data) {
            /** @var Tenant $tenant */
            $tenant = Tenant::updateOrCreate(
                ['domain' => $data['domain']],
                [
                    'name' => $data['name'],
                    'status' => 'active',
                    'timezone' => 'Europe/Kyiv',
                    'locale' => $data['locale'],
                    'created_by' => $owner->id,
                    'updated_by' => $owner->id,
                    'uuid' => Tenant::where('domain', $data['domain'])
                        ->value('uuid')
                                      ?? (string) Str::uuid(),
                ]
            );

            // Прив’язуємо власника до всіх, крім «free» тенантів
            if (! in_array($data['domain'], $freeDomains, true)) {
                $tenant->users()->syncWithoutDetaching([
                    $owner->id => ['is_owner' => true],
                ]);
            }
        }
    }
}
