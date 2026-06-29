<?php

declare(strict_types=1);

namespace App\Domain\Categories\ValueObjects;

enum CategoryType: string
{
    case REVENUE = 'revenue';
    case EXPENSE = 'expense';
    case TRANSFER = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::REVENUE => 'Receita',
            self::EXPENSE => 'Despesa',
            self::TRANSFER => 'Transferência',
        };
    }

    public function isRevenue(): bool
    {
        return $this === self::REVENUE;
    }

    public function isExpense(): bool
    {
        return $this === self::EXPENSE;
    }

    public function isTransfer(): bool
    {
        return $this === self::TRANSFER;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
