<?php

declare(strict_types=1);

namespace App\Domain\FinancialProjections;

final readonly class ProjectionPeriod
{
    public function __construct(
        private \DateTimeImmutable $startDate,
        private \DateTimeImmutable $endDate,
        private PeriodType $periodType,
    ) {
        $this->validate();
    }

    public static function createMonthly(string $yearMonth): self
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m', $yearMonth);
        if ($date === false) {
            throw new \InvalidArgumentException('Invalid year-month format. Use YYYY-MM.');
        }

        $startDate = $date->modify('first day of this month')->setTime(0, 0, 0);
        $endDate = $date->modify('last day of this month')->setTime(23, 59, 59);

        return new self($startDate, $endDate, PeriodType::MONTHLY);
    }

    public static function createQuarterly(string $year, int $quarter): self
    {
        if ($quarter < 1 || $quarter > 4) {
            throw new \InvalidArgumentException('Quarter must be between 1 and 4.');
        }

        $month = ($quarter - 1) * 3 + 1;
        $startDate = \DateTimeImmutable::createFromFormat('Y-m-d', "$year-$month-01")->setTime(0, 0, 0);
        $endDate = $startDate->modify('+3 months -1 day')->setTime(23, 59, 59);

        return new self($startDate, $endDate, PeriodType::QUARTERLY);
    }

    public static function createYearly(string $year): self
    {
        $startDate = \DateTimeImmutable::createFromFormat('Y-m-d', "$year-01-01")->setTime(0, 0, 0);
        $endDate = \DateTimeImmutable::createFromFormat('Y-m-d', "$year-12-31")->setTime(23, 59, 59);

        return new self($startDate, $endDate, PeriodType::YEARLY);
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getPeriodType(): PeriodType
    {
        return $this->periodType;
    }

    public function getLabel(): string
    {
        return match ($this->periodType) {
            PeriodType::MONTHLY => $this->startDate->format('F Y'),
            PeriodType::QUARTERLY => 'Q' . ceil($this->startDate->format('m') / 3) . ' ' . $this->startDate->format('Y'),
            PeriodType::YEARLY => $this->startDate->format('Y'),
        };
    }

    private function validate(): void
    {
        if ($this->startDate > $this->endDate) {
            throw new \InvalidArgumentException('Start date must be before end date.');
        }
    }

    public function getDurationInDays(): int
    {
        $interval = $this->startDate->diff($this->endDate);
        return (int) $interval->days + 1;
    }

    public function contains(\DateTimeImmutable $date): bool
    {
        return $date >= $this->startDate && $date <= $this->endDate;
    }

    public function overlaps(self $other): bool
    {
        return $this->startDate <= $other->endDate && $this->endDate >= $other->startDate;
    }
}