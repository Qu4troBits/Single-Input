<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Infrastructure\Persistence\Eloquent\Scopes\TenantDataScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $bank_account_id
 * @property string $category_id
 * @property string $description
 * @property string $amount
 * @property string $direction
 * @property string $status
 * @property string $competence_month
 * @property \Illuminate\Support\Carbon|null $payment_date
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class TransactionModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'transactions';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'bank_account_id',
        'category_id',
        'description',
        'amount',
        'direction',
        'status',
        'competence_month',
        'payment_date',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantDataScope());
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccountModel::class, 'bank_account_id');
    }

    public function category()
    {
        return $this->belongsTo(CategoryModel::class, 'category_id');
    }
}