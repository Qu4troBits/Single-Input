<?php

declare(strict_types=1);

namespace App\Domain\Transactions;

enum TransactionStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case REVERSED = 'reversed';

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }

    public function canBePaid(): bool
    {
        return $this === self::PENDING;
    }

    public function canBeCancelled(): bool
    {
        return $this === self::PENDING || $this === self::PAID;
    }
}