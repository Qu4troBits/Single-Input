<?php

declare(strict_types=1);

namespace App\Domain\Reports;

enum DreLineType: string
{
    case REVENUE = 'revenue';
    case EXPENSE = 'expense';
    case PROFIT = 'profit';

    public function getLabel(): string
    {
        return match ($this) {
            self::REVENUE => 'Receita',
            self::EXPENSE => 'Despesa',
            self::PROFIT => 'Lucro',
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

    public function isProfit(): bool
    {
        return $this === self::PROFIT;
    }

    public function getSign(): int
    {
        return match ($this) {
            self::REVENUE, self::PROFIT => 1,
            self::EXPENSE => -1,
        };
    }
}