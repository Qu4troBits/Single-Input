<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Infrastructure\Persistence\Eloquent\Scopes\TenantDataScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class BankAccountModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bank_accounts';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'type',
        'bank_code',
        'bank_name',
        'agency_number',
        'account_number',
        'account_digit',
        'initial_balance',
        'current_balance',
        'status',
        'description',
        'color',
        'icon',
        'include_in_dashboard',
        'include_in_reports',
        'is_default',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'include_in_dashboard' => 'boolean',
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

    public function transactions(): HasMany
    {
        return $this->hasMany(TransactionModel::class, 'bank_account_id', 'id');
    }

    // public function dailyReconciliations(): HasMany
    // {
    //     return $this->hasMany(DailyReconciliationModel::class, 'bank_account_id', 'id');
    // }
}
