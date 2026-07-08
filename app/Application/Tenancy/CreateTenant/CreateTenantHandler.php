<?php

declare(strict_types=1);

namespace App\Application\Tenancy\CreateTenant;

use App\Application\Tenancy\Ports\InitialTenantAdminCreatorInterface;
use App\Application\Tenancy\Ports\TenantSchemaManagerInterface;
use App\Console\Commands\TenantCreateCommand;
use App\Domain\Plans\PlanRepositoryInterface;
use App\Domain\Tenancy\Tenant;
use App\Domain\Tenancy\TenantRepositoryInterface;
use App\Domain\Tenancy\ValueObjects\TenantSlug;
use RuntimeException;

final readonly class CreateTenantHandler
{
    public function __construct(
        private PlanRepositoryInterface $plans,
        private TenantRepositoryInterface $tenants,
        private TenantSchemaManagerInterface $schemaManager,
        private InitialTenantAdminCreatorInterface $adminCreator,
        private TenantCreateCommand $createCommand,
    ) {}

    public function handle(CreateTenantData $data): Tenant
    {
        $plan = $this->plans->findBySlug($data->planSlug);

        if ($plan === null) {
            throw new RuntimeException('Plan not found.');
        }

        $slug = TenantSlug::fromString($data->slug);

        $existing = $this->tenants->findBySlug($slug);

        if ($existing !== null) {
            throw new RuntimeException('Tenant already exists.');
        }

        $tenant = $this->createCommand->createTenant($slug, $data->name, $plan->id);

        $this->schemaManager->createSchema($tenant->schemaName);
        $this->schemaManager->runTenantMigrations($tenant->schemaName);
        $this->adminCreator->createInitialAdmin($tenant, $data->adminName, $data->adminEmail, $data->adminPassword);

        return $tenant;
    }
}
