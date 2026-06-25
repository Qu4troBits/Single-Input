<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

final class TenantModel extends Model
{
    protected $table = 'tenants';

    protected $fillable = [
        'slug',
        'name',
        'db_schema',
        'plan_id',
    ];
}
