<?php

declare(strict_types=1);

namespace App\Domain\Categories\ValueObjects;

enum CategoryType: string
{
    case REVENUE = 'revenue';
    case INCOME = 'income'; // Alias for REVENUE for backward compatibility
    case EXPENSE = 'expense';
    case TRANSFER = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::REVENUE, self::INCOME => 'Receita',
            self::EXPENSE => 'Despesa',
            self::TRANSFER => 'Transferência',
        };
    }

    public function isRevenue(): bool
    {
        return in_array($this, [self::REVENUE, self::INCOME], true);
    }

    public function isExpense(): bool
    {
        return $this === self::EXPENSE;
    }

    public function isTransfer(): bool
    {
        return $this === self::TRANSFER;
    }

    public function isIncome(): bool
    {
        return $this->isRevenue();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
