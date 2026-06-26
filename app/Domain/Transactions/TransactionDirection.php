<?php

declare(strict_types=1);

namespace App\Domain\Transactions;

enum TransactionDirection: string
{
    case IN = 'in';
    case OUT = 'out';

    public function isIncome(): bool
    {
        return $this === self::IN;
    }

    public function isExpense(): bool
    {
        return $this === self::OUT;
    }

    public function getSign(): int
    {
        return $this->isIncome() ? 1 : -1;
    }
}