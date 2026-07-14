<?php

declare(strict_types=1);

namespace App\Domain\FinancialProjections;

enum PeriodType: string
{
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';
    case YEARLY = 'yearly';

    public function getLabel(): string
    {
        return match ($this) {
            self::MONTHLY => 'Mensal',
            self::QUARTERLY => 'Trimestral',
            self::YEARLY => 'Anual',
        };
    }

    public function getMonths(): int
    {
        return match ($this) {
            self::MONTHLY => 1,
            self::QUARTERLY => 3,
            self::YEARLY => 12,
        };
    }
}