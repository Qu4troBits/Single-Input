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
 * @property string $projection_id
 * @property \DateTimeInterface $date
 * @property string $description
 * @property string $amount
 * @property string $type
 * @property string|null $category_id
 * @property string|null $category_name
 * @property string|null $notes
 * @property string|null $source
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 * @property \DateTimeInterface|null $deleted_at
 * @property FinancialProjectionModel $projection
 * @property CategoryModel|null $category
 */
final class ProjectionItemModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'projection_items';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'projection_id',
        'date',
        'description',
        'amount',
        'type',
        'category_id',
        'category_name',
        'notes',
        'source',
    ];

    protected $casts = [
        'date' => 'datetime',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantDataScope());
    }

    public function projection(): BelongsTo
    {
        return $this->belongsTo(FinancialProjectionModel::class, 'projection_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'category_id');
    }
}