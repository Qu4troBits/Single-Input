<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Tenancy\Tenant;
use App\Domain\Tenancy\TenantRepositoryInterface;
use App\Domain\Tenancy\ValueObjects\TenantId;
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

        return $this->mapModelToEntity($model);
    }

    public function findById(TenantId $id): ?Tenant
    {
        DB::statement('SET search_path TO public');

        $model = TenantModel::query()->find($id->toString());

        if ($model === null) {
            return null;
        }

        return $this->mapModelToEntity($model);
    }

    public function save(Tenant $tenant): void
    {
        DB::statement('SET search_path TO public');

        TenantModel::query()->updateOrCreate(
            ['id' => $tenant->id->toString()],
            [
                'slug' => $tenant->slug->toString(),
                'name' => $tenant->name,
                'db_schema' => $tenant->schemaName->toString(),
                'plan_id' => $tenant->planId->toString(),
                'created_at' => $tenant->createdAt,
            ]
        );
    }

    private function mapModelToEntity(TenantModel $model): Tenant
    {
        return Tenant::create(
            id: TenantId::fromString($model->getAttribute('id')),
            slug: $model->getAttribute('slug'),
            name: $model->getAttribute('name'),
            planId: \App\Domain\Plans\PlanId::fromString($model->getAttribute('plan_id')),
            dbSchema: $model->getAttribute('db_schema'),
        );
    }
}
