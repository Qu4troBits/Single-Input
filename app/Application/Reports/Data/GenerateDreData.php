<?php

declare(strict_types=1);

namespace App\Application\Reports\Data;

final readonly class GenerateDreData
{
    public function __construct(
        public string $periodType, // 'monthly', 'quarterly', 'yearly', 'custom'
        public string $yearMonth = '', // Para monthly: '2024-01'
        public string $year = '', // Para yearly: '2024'
        public int $quarter = 0, // Para quarterly: 1, 2, 3, 4
        public ?\DateTimeImmutable $startDate = null, // Para custom
        public ?\DateTimeImmutable $endDate = null, // Para custom
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        switch ($this->periodType) {
            case 'monthly':
                if (empty($this->yearMonth)) {
                    throw new \InvalidArgumentException('Year-month is required for monthly reports.');
                }
                if (!preg_match('/^\d{4}-\d{2}$/', $this->yearMonth)) {
                    throw new \InvalidArgumentException('Year-month must be in YYYY-MM format.');
                }
                break;

            case 'quarterly':
                if (empty($this->year)) {
                    throw new \InvalidArgumentException('Year is required for quarterly reports.');
                }
                if ($this->quarter < 1 || $this->quarter > 4) {
                    throw new \InvalidArgumentException('Quarter must be between 1 and 4.');
                }
                break;

            case 'yearly':
                if (empty($this->year)) {
                    throw new \InvalidArgumentException('Year is required for yearly reports.');
                }
                if (!preg_match('/^\d{4}$/', $this->year)) {
                    throw new \InvalidArgumentException('Year must be in YYYY format.');
                }
                break;

            case 'custom':
                if ($this->startDate === null || $this->endDate === null) {
                    throw new \InvalidArgumentException('Start date and end date are required for custom reports.');
                }
                if ($this->startDate > $this->endDate) {
                    throw new \InvalidArgumentException('Start date must be before end date.');
                }
                break;

            default:
                throw new \InvalidArgumentException(sprintf(
                    'Invalid period type: %s. Must be one of: monthly, quarterly, yearly, custom.',
                    $this->periodType
                ));
        }
    }

    public static function forMonthly(string $yearMonth): self
    {
        return new self('monthly', yearMonth: $yearMonth);
    }

    public static function forQuarterly(string $year, int $quarter): self
    {
        return new self('quarterly', year: $year, quarter: $quarter);
    }

    public static function forYearly(string $year): self
    {
        return new self('yearly', year: $year);
    }

    public static function forCustom(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): self
    {
        return new self('custom', startDate: $startDate, endDate: $endDate);
    }
}