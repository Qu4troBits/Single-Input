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
 * @property string $bank_account_id
 * @property \DateTimeInterface $date
 * @property string $description
 * @property string $amount
 * @property string $status
 * @property string|null $transaction_id
 * @property string|null $bank_statement_id
 * @property string|null $notes
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 * @property \DateTimeInterface|null $deleted_at
 * @property BankAccountModel|null $bankAccount
 * @property TransactionModel|null $transaction
 */
final class ReconciliationItemModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'reconciliation_items';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'bank_account_id',
        'date',
        'description',
        'amount',
        'status',
        'transaction_id',
        'bank_statement_id',
        'notes',
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

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccountModel::class, 'bank_account_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(TransactionModel::class, 'transaction_id');
    }
}