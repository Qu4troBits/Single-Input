<?php

declare(strict_types=1);

namespace App\Application\Tenancy\CreateTenant;

final readonly class CreateTenantData
{
    public function __construct(
        public string $slug,
        public string $name,
        public string $planSlug,
        public string $adminName,
        public string $adminEmail,
        public string $adminPassword,
    ) {
    }
}
