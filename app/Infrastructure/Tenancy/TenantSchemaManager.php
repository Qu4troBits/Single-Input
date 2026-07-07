<?php

declare(strict_types=1);

namespace App\Infrastructure\Tenancy;

use App\Application\Tenancy\Ports\TenantSchemaManagerInterface;
use App\Domain\Tenancy\ValueObjects\TenantSchemaName;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

final readonly class TenantSchemaManager implements TenantSchemaManagerInterface
{
    public function createSchema(TenantSchemaName $schemaName): void
    {
        DB::statement(sprintf('CREATE SCHEMA IF NOT EXISTS "%s"', $this->escapeIdentifier($schemaName->toString())));
    }

    public function createTenantSchema(string $schemaName): void
    {
        DB::statement(sprintf('CREATE SCHEMA IF NOT EXISTS "%s"', $this->escapeIdentifier($schemaName)));
    }

    public function dropTenantSchema(string $schemaName): void
    {
        DB::statement(sprintf('DROP SCHEMA IF EXISTS "%s" CASCADE', $this->escapeIdentifier($schemaName)));
    }

    public function runTenantMigrations(TenantSchemaName $schemaName): void
    {
        DB::statement(sprintf(
            'SET search_path TO "%s", public',
            $this->escapeIdentifier($schemaName->toString())
        ));

        Artisan::call('migrate', [
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        if (DB::table('bank_accounts')->count() === 0) {
            DB::table('bank_accounts')->insert([
                'name' => 'Principal',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (DB::table('categories')->count() === 0) {
            DB::table('categories')->insert([
                'name' => 'Geral',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function escapeIdentifier(string $identifier): string
    {
        return str_replace('"', '""', $identifier);
    }
}
