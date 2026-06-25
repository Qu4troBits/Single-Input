<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models\Tenant;

final class CategoryModel extends TenantDataModel
{
    protected $table = 'categories';

    protected $fillable = [
        'name',
    ];
}
