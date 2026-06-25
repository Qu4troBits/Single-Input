<?php

declare(strict_types=1);

namespace App\Domain\Transactions;

enum TransactionDirection: string
{
    case In = 'in';
    case Out = 'out';
}
