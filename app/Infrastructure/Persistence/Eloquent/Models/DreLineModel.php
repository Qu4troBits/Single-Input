<?php

declare(strict_types=1); 

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Infrastructure\Persistence\Eloquent\Scopes\TenantDataScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class DreLineModel extends Model
{
    use SoftDeletes;

    protected $table = 'dre_lines';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'dre_id',
        'code',
        'description',
        'amount',
        'type',
        'level',
        'is_operating',
        'parent_id',
        'category_id',
        'category_name',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'level' => 'integer',
        'is_operating' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantDataScope());
    }

    public function dre(): BelongsTo
    {
        return $this->belongsTo(DreModel::class, 'dre_id', 'id');
    }
}