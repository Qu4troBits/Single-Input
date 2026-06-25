<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models\Tenant;

final class BankAccountModel extends TenantDataModel
{
    protected $table = 'bank_accounts';

    protected $fillable = [
        'name',
    ];
}
