<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Tenancy\Tenant;
use App\Domain\Tenancy\TenantRepositoryInterface;
use App\Domain\Tenancy\ValueObjects\TenantSchemaName;
use App\Domain\Tenancy\ValueObjects\TenantSlug;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Illuminate\Support\Facades\DB;

final readonly class EloquentTenantRepository implements TenantRepositoryInterface
{
    public function findBySlug(TenantSlug $slug): ?Tenant
    {
        DB::statement('SET search_path TO public');

        $model = TenantModel::query()->where('slug', $slug->toString())->first();

        if ($model === null) {
            return null;
        }

        return new Tenant(
            id: (int) $model->getAttribute('id'),
            slug: TenantSlug::fromString((string) $model->getAttribute('slug')),
            name: (string) $model->getAttribute('name'),
            schemaName: new TenantSchemaName((string) $model->getAttribute('db_schema')),
            planId: (int) $model->getAttribute('plan_id'),
        );
    }

    public function create(TenantSlug $slug, string $name, int $planId): Tenant
    {
        DB::statement('SET search_path TO public');

        $schemaName = TenantSchemaName::forSlug($slug);

        $model = TenantModel::query()->create([
            'slug' => $slug->toString(),
            'name' => $name,
            'db_schema' => $schemaName->toString(),
            'plan_id' => $planId,
        ]);

        return new Tenant(
            id: (int) $model->getAttribute('id'),
            slug: $slug,
            name: (string) $model->getAttribute('name'),
            schemaName: $schemaName,
            planId: (int) $model->getAttribute('plan_id'),
        );
    }
}
