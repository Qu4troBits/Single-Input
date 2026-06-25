<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models\Tenant;

final class TransactionModel extends TenantDataModel
{
    protected $table = 'transactions';

    protected $fillable = [
        'bank_account_id',
        'category_id',
        'description',
        'amount',
        'direction',
        'status',
        'competence_month',
        'payment_date',
    ];
}
