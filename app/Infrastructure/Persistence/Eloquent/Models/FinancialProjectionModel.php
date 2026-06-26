<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Infrastructure\Persistence\Eloquent\Scopes\TenantDataScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $type
 * @property string $period_type
 * @property string $year_month
 * @property string $year
 * @property int $quarter
 * @property string|null $category_id
 * @property string $scenario
 * @property string $title
 * @property string|null $notes
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 * @property \DateTimeInterface|null $deleted_at
 * @property CategoryModel|null $category
 */
final class FinancialProjectionModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'financial_projections';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'type',
        'period_type',
        'year_month',
        'year',
        'quarter',
        'category_id',
        'scenario',
        'title',
        'notes',
    ];

    protected $casts = [
        'quarter' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantDataScope());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'category_id');
    }

    public function items()
    {
        return $this->hasMany(ProjectionItemModel::class, 'projection_id');
    }
}