<?php

declare(strict_types=1);

namespace App\Domain\BankAccounts;

enum BankAccountStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case CLOSED = 'closed';
}