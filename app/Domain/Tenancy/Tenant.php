<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

use App\Domain\Tenancy\ValueObjects\TenantSchemaName;
use App\Domain\Tenancy\ValueObjects\TenantSlug;

final readonly class Tenant
{
    public function __construct(
        public int $id,
        public TenantSlug $slug,
        public string $name,
        public TenantSchemaName $schemaName,
        public int $planId,
    ) {
    }
}
