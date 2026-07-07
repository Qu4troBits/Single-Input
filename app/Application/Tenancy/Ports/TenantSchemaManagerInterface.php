<?php

declare(strict_types=1);

namespace App\Application\Tenancy\Ports;

use App\Domain\Tenancy\ValueObjects\TenantSchemaName;

interface TenantSchemaManagerInterface
{
    public function createSchema(TenantSchemaName $schemaName): void;

    public function createTenantSchema(string $schemaName): void;

    public function runTenantMigrations(TenantSchemaName $schemaName): void;

    public function dropTenantSchema(string $schemaName): void;
}
