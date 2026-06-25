<?php

declare(strict_types=1);

namespace App\Infrastructure\Tenancy;

use App\Application\Tenancy\Ports\InitialTenantAdminCreatorInterface;
use App\Domain\Tenancy\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final readonly class InitialTenantAdminCreator implements InitialTenantAdminCreatorInterface
{
    public function createInitialAdmin(Tenant $tenant, string $name, string $email, string $plainPassword): void
    {
        DB::statement('SET search_path TO public');

        User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($plainPassword),
            'is_admin' => true,
        ]);
    }
}
