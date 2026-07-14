<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantDataScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // For schema-per-tenant, the scope doesn't need to filter by tenant_id
        // because the search_path is already set to the tenant's schema
        // This scope is kept for future flexibility or if we need to add other constraints
    }
}
