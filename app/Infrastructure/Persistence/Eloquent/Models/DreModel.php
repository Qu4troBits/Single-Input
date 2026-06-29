<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Infrastructure\Persistence\Eloquent\Scopes\TenantDataScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class DreModel extends Model
{
    use SoftDeletes;

    protected $table = 'dres';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'period_start',
        'period_end',
        'period_type',
        'title',
        'category_id',
        'scenario',
        'total_revenue',
        'total_expenses',
        'net_profit',
        'gross_profit',
        'operating_profit',
        'ebitda',
        'ebit',
        'generated_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'generated_at' => 'datetime',
        'total_revenue' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'net_profit' => 'decimal:2',
        'gross_profit' => 'decimal:2',
        'operating_profit' => 'decimal:2',
        'ebitda' => 'decimal:2',
        'ebit' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantDataScope());
    }

    public function lines(): HasMany
    {
        return $this->hasMany(DreLineModel::class, 'dre_id', 'id');
    }
}