<?php

declare(strict_types=1);

namespace App\Application\Reports\DTOs;

final readonly class GenerateDreData
{
    public function __construct(
        public string $periodType,
        public string $yearMonth = '',
        public string $year = '',
        public int $quarter = 0,
        public ?string $categoryId = null,
        public string $scenario = 'base',
        public ?string $startDate = null,
        public ?string $endDate = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $validPeriodTypes = ['monthly', 'quarterly', 'yearly', 'custom'];
        if (!in_array($this->periodType, $validPeriodTypes, true)) {
            throw new \InvalidArgumentException('Invalid period type. Must be one of: ' . implode(', ', $validPeriodTypes));
        }

        if ($this->periodType === 'monthly') {
            if (empty($this->yearMonth)) {
                throw new \InvalidArgumentException('Year-month is required for monthly period.');
            }

            $date = \DateTimeImmutable::createFromFormat('Y-m', $this->yearMonth);
            if ($date === false) {
                throw new \InvalidArgumentException('Invalid year-month format. Use YYYY-MM.');
            }
        }

        if ($this->periodType === 'quarterly') {
            if (empty($this->year)) {
                throw new \InvalidArgumentException('Year is required for quarterly period.');
            }

            if ($this->quarter < 1 || $this->quarter > 4) {
                throw new \InvalidArgumentException('Quarter must be between 1 and 4.');
            }
        }

        if ($this->periodType === 'yearly') {
            if (empty($this->year)) {
                throw new \InvalidArgumentException('Year is required for yearly period.');
            }

            if (!is_numeric($this->year) || (int) $this->year < 2000 || (int) $this->year > 2100) {
                throw new \InvalidArgumentException('Year must be between 2000 and 2100.');
            }
        }

        if ($this->periodType === 'custom') {
            if (empty($this->startDate) || empty($this->endDate)) {
                throw new \InvalidArgumentException('Start date and end date are required for custom period.');
            }

            $startDate = \DateTimeImmutable::createFromFormat('Y-m-d', $this->startDate);
            $endDate = \DateTimeImmutable::createFromFormat('Y-m-d', $this->endDate);

            if ($startDate === false) {
                throw new \InvalidArgumentException('Invalid start date format. Use YYYY-MM-DD.');
            }

            if ($endDate === false) {
                throw new \InvalidArgumentException('Invalid end date format. Use YYYY-MM-DD.');
            }

            if ($endDate < $startDate) {
                throw new \InvalidArgumentException('End date must be after start date.');
            }
        }

        $validScenarios = ['base', 'optimistic', 'pessimistic', 'custom'];
        if (!in_array($this->scenario, $validScenarios, true)) {
            throw new \InvalidArgumentException('Invalid scenario. Must be one of: ' . implode(', ', $validScenarios));
        }

        if ($this->categoryId !== null && empty($this->categoryId)) {
            throw new \InvalidArgumentException('Category ID cannot be empty.');
        }
    }

    public function getPeriodType(): string
    {
        return $this->periodType;
    }

    public function getYearMonth(): ?string
    {
        return $this->yearMonth ?: null;
    }

    public function getYear(): ?string
    {
        return $this->year ?: null;
    }

    public function getQuarter(): ?int
    {
        return $this->quarter ?: null;
    }

    public function getCategoryId(): ?string
    {
        return $this->categoryId;
    }

    public function getScenario(): string
    {
        return $this->scenario;
    }

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function toArray(): array
    {
        return [
            'period_type' => $this->periodType,
            'year_month' => $this->yearMonth,
            'year' => $this->year,
            'quarter' => $this->quarter,
            'quarterly' => $this->quarter,
            'category_id' => $this->categoryId,
            'scenario' => $this->scenario,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ];
    }

    public static function forMonthly(string $yearMonth, ?string $categoryId = null, string $scenario = 'base'): self
    {
        return new self(
            periodType: 'monthly',
            yearMonth: $yearMonth,
            categoryId: $categoryId,
            scenario: $scenario,
        );
    }

    public static function forQuarterly(string $year, int $quarter, ?string $categoryId = null, string $scenario = 'base'): self
    {
        return new self(
            periodType: 'quarterly',
            year: $year,
            quarter: $quarter,
            categoryId: $categoryId,
            scenario: $scenario,
        );
    }

    public static function forYearly(string $year, ?string $categoryId = null, string $scenario = 'base'): self
    {
        return new self(
            periodType: 'yearly',
            year: $year,
            categoryId: $categoryId,
            scenario: $scenario,
        );
    }

    public static function forCustom(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, ?string $categoryId = null, string $scenario = 'base'): self
    {
        return new self(
            periodType: 'custom',
            startDate: $startDate->format('Y-m-d'),
            endDate: $endDate->format('Y-m-d'),
            categoryId: $categoryId,
            scenario: $scenario,
        );
    }
}