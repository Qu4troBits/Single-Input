<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Plans\ValueObjects\PlanId;
use App\Domain\Tenancy\Tenant;
use App\Domain\Tenancy\ValueObjects\TenantId;
use App\Domain\Tenancy\TenantRepositoryInterface;
use App\Domain\Tenancy\ValueObjects\TenantSlug;
use App\Infrastructure\Tenancy\TenantSchemaManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class TenantCreateCommand extends Command
{
    protected $signature = 'tenant:create
                            {slug : Unique identifier for the tenant}
                            {name : Display name for the tenant}
                            {plan : Plan ID for the tenant subscription}';

    protected $description = 'Create a new tenant with dedicated schema in PostgreSQL';

    public function handle(
        TenantRepositoryInterface $tenantRepository,
        TenantSchemaManager $schemaManager,
    ): int {
        $slug = $this->argument('slug');
        $name = $this->argument('name');
        $planId = $this->argument('plan');

        // Validate slug format
        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            $this->error('Slug must contain only lowercase letters, numbers, and hyphens.');
            return self::FAILURE;
        }

        // Check if tenant already exists
        $existingTenant = $tenantRepository->findBySlug(TenantSlug::fromString($slug));
        if ($existingTenant !== null) {
            $this->error("Tenant with slug '{$slug}' already exists.");
            return self::FAILURE;
        }

        DB::beginTransaction();

        try {
            // Create tenant entity
            $tenant = Tenant::create(
                id: TenantId::generate(),
                slug: $slug,
                name: $name,
                planId: PlanId::fromString($planId),
                dbSchema: "tenant_{$slug}",
            );

            // Create database schema
            $schemaManager->createTenantSchema($tenant->schemaName->toString());

            // Save tenant to repository
            $tenantRepository->save($tenant);

            // Run tenant migrations
            $this->call('migrate', [
                '--path' => 'database/migrations/tenant',
                '--database' => 'tenant',
            ]);

            DB::commit();

            $this->info("Tenant '{$name}' ({$slug}) created successfully.");
            $this->info("Database schema: tenant_{$slug}");
            $this->info("Tenant ID: {$tenant->id->toString()}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $schemaManager->dropTenantSchema("tenant_{$slug}");

            $this->error("Failed to create tenant: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
