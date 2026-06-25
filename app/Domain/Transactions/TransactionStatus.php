<?php

declare(strict_types=1);

namespace App\Domain\Transactions;

enum TransactionStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
}
