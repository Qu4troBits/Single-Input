<?php

declare(strict_types=1);

namespace App\Application\FinancialProjections\DTOs;

use App\Domain\FinancialProjections\ProjectionType;

final readonly class GenerateProjectionData
{
    public function __construct(
        public ProjectionType $type,
        public string $periodType, // 'monthly', 'quarterly', 'yearly'
        public string $yearMonth = '', // Para monthly: '2024-01'
        public string $year = '', // Para yearly: '2024'
        public int $quarter = 0, // Para quarterly: 1, 2, 3, 4
        public ?string $categoryId = null,
        public string $scenario = 'base',
        public ?string $notes = null,
    ) {
        $this->validate();
    }

    public static function forMonthlyRevenue(string $yearMonth, ?string $categoryId = null): self
    {
        return new self(
            type: ProjectionType::REVENUE,
            periodType: 'monthly',
            yearMonth: $yearMonth,
            categoryId: $categoryId,
        );
    }

    public static function forMonthlyExpense(string $yearMonth, ?string $categoryId = null): self
    {
        return new self(
            type: ProjectionType::EXPENSE,
            periodType: 'monthly',
            yearMonth: $yearMonth,
            categoryId: $categoryId,
        );
    }

    public static function forQuarterlyProfit(string $year, int $quarter): self
    {
        return new self(
            type: ProjectionType::PROFIT,
            periodType: 'quarterly',
            year: $year,
            quarter: $quarter,
        );
    }

    public static function forYearlyCashFlow(string $year): self
    {
        return new self(
            type: ProjectionType::CASH_FLOW,
            periodType: 'yearly',
            year: $year,
        );
    }

    private function validate(): void
    {
        $validPeriodTypes = ['monthly', 'quarterly', 'yearly'];
        if (!in_array($this->periodType, $validPeriodTypes, true)) {
            throw new \InvalidArgumentException('Invalid period type.');
        }

        if ($this->periodType === 'monthly' && empty($this->yearMonth)) {
            throw new \InvalidArgumentException('Year-month is required for monthly projections.');
        }

        if ($this->periodType === 'quarterly' && (empty($this->year) || $this->quarter < 1 || $this->quarter > 4)) {
            throw new \InvalidArgumentException('Valid year and quarter (1-4) are required for quarterly projections.');
        }

        if ($this->periodType === 'yearly' && empty($this->year)) {
            throw new \InvalidArgumentException('Year is required for yearly projections.');
        }

        $validScenarios = ['base', 'optimistic', 'pessimistic', 'custom'];
        if (!in_array($this->scenario, $validScenarios, true)) {
            throw new \InvalidArgumentException('Invalid scenario.');
        }
    }
}