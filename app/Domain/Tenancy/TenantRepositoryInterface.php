<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

use App\Domain\Tenancy\ValueObjects\TenantId;
use App\Domain\Tenancy\ValueObjects\TenantSlug;

interface TenantRepositoryInterface
{
    public function findBySlug(TenantSlug $slug): ?Tenant;

    public function findById(TenantId $id): ?Tenant;

    public function save(Tenant $tenant): void;
}
