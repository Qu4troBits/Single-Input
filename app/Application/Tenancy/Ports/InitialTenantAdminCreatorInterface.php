<?php

declare(strict_types=1);

namespace App\Application\Tenancy\Ports;

use App\Domain\Tenancy\Tenant;

interface InitialTenantAdminCreatorInterface
{
    public function createInitialAdmin(Tenant $tenant, string $name, string $email, string $plainPassword): void;
}
