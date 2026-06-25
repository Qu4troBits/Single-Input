<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Infrastructure\Persistence\Eloquent\Scopes\TenantDataScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $name
 * @property string $type
 * @property string $status
 * @property string|null $bank_code
 * @property string|null $agency
 * @property string|null $account_number
 * @property string|null $account_digit
 * @property string|null $description
 * @property string $balance
 * @property string $initial_balance
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class BankAccountModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'bank_accounts';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'type',
        'status',
        'bank_code',
        'agency',
        'account_number',
        'account_digit',
        'description',
        'balance',
        'initial_balance',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'initial_balance' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantDataScope());
    }
}