<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

interface TenantContextInterface
{
    public function set(Tenant $tenant): void;

    public function tenant(): ?Tenant;
}
