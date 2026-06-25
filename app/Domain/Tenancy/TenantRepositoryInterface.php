<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

use App\Domain\Tenancy\ValueObjects\TenantSlug;

interface TenantRepositoryInterface
{
    public function findBySlug(TenantSlug $slug): ?Tenant;

    public function create(TenantSlug $slug, string $name, int $planId): Tenant;
}
