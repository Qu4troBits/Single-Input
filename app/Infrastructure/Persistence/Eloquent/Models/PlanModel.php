<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

final class PlanModel extends Model
{
    protected $table = 'plans';

    protected $fillable = [
        'slug',
        'name',
    ];
}
