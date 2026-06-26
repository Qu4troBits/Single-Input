<?php

declare(strict_types=1);

namespace App\Domain\FinancialProjections;

enum ProjectionType: string
{
    case REVENUE = 'revenue';
    case EXPENSE = 'expense';
    case PROFIT = 'profit';
    case CASH_FLOW = 'cash_flow';
    case BALANCE_SHEET = 'balance_sheet';

    public function getLabel(): string
    {
        return match ($this) {
            self::REVENUE => 'Receita',
            self::EXPENSE => 'Despesa',
            self::PROFIT => 'Lucro',
            self::CASH_FLOW => 'Fluxo de Caixa',
            self::BALANCE_SHEET => 'Balanço Patrimonial',
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

    public function isCashFlow(): bool
    {
        return $this === self::CASH_FLOW;
    }

    public function isBalanceSheet(): bool
    {
        return $this === self::BALANCE_SHEET;
    }
}