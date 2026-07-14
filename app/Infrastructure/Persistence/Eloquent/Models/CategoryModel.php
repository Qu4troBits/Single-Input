<?php

declare(strict_types=1); 

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Infrastructure\Persistence\Eloquent\Scopes\TenantDataScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class CategoryModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categories';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'type',
        'code',
        'description',
        'color',
        'icon',
        'is_operating',
        'is_tax_deductible',
        'include_in_reports',
        'is_default',
        'parent_id',
    ];

    protected $casts = [
        'is_operating' => 'boolean',
        'is_tax_deductible' => 'boolean',
        'include_in_reports' => 'boolean',
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantDataScope());
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'parent_id', 'id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CategoryModel::class, 'parent_id', 'id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(TransactionModel::class, 'category_id', 'id');
    }

    public function dreLines(): HasMany
    {
        return $this->hasMany(DreLineModel::class, 'category_id', 'id');
    }
}
