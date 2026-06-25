<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

abstract class TenantDataModel extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope('tenant_search_path', function (Builder $builder): void {
            $row = DB::selectOne('SHOW search_path');

            $searchPath = is_object($row) && property_exists($row, 'search_path')
                ? (string) $row->search_path
                : '';

            if ($searchPath === '' || ! str_contains($searchPath, 'tenant_')) {
                throw new RuntimeException('Tenant search_path is not configured.');
            }
        });
    }
}
