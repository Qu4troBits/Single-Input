<?php

declare(strict_types=1);

namespace App\Domain\Reports;

use App\Domain\Shared\Money;

final readonly class ReportPeriod
{
    public function __construct(
        private \DateTimeImmutable $startDate,
        private \DateTimeImmutable $endDate,
    ) {
        if ($this->startDate > $this->endDate) {
            throw new \InvalidArgumentException('Start date must be before end date.');
        }
    }

    public static function createMonthly(string $yearMonth): self
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m', $yearMonth);
        if ($date === false) {
            throw new \InvalidArgumentException('Invalid year-month format. Use YYYY-MM.');
        }

        $startDate = $date->modify('first day of this month')->setTime(0, 0, 0);
        $endDate = $date->modify('last day of this month')->setTime(23, 59, 59);

        return new self($startDate, $endDate);
    }

    public static function createQuarterly(string $year, int $quarter): self
    {
        if ($quarter < 1 || $quarter > 4) {
            throw new \InvalidArgumentException('Quarter must be between 1 and 4.');
        }

        $month = ($quarter - 1) * 3 + 1;
        $startDate = \DateTimeImmutable::createFromFormat('Y-m-d', "{$year}-{$month}-01")->setTime(0, 0, 0);
        $endDate = $startDate->modify('+2 months')->modify('last day of this month')->setTime(23, 59, 59);

        return new self($startDate, $endDate);
    }

    public static function createYearly(string $year): self
    {
        $startDate = \DateTimeImmutable::createFromFormat('Y-m-d', "{$year}-01-01")->setTime(0, 0, 0);
        $endDate = \DateTimeImmutable::createFromFormat('Y-m-d', "{$year}-12-31")->setTime(23, 59, 59);

        return new self($startDate, $endDate);
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getDays(): int
    {
        $interval = $this->startDate->diff($this->endDate);
        return (int) $interval->format('%a') + 1; // +1 para incluir o último dia
    }

    public function getMonths(): int
    {
        $interval = $this->startDate->diff($this->endDate);
        $months = $interval->y * 12 + $interval->m;
        
        // Se o intervalo inclui parte de um mês adicional, adiciona 1
        if ($interval->d > 0 || $interval->h > 0 || $interval->i > 0 || $interval->s > 0) {
            $months++;
        }
        
        return $months;
    }

    public function contains(\DateTimeImmutable $date): bool
    {
        return $date >= $this->startDate && $date <= $this->endDate;
    }

    public function overlaps(self $other): bool
    {
        return $this->startDate <= $other->endDate && $this->endDate >= $other->startDate;
    }

    public function toString(): string
    {
        return sprintf(
            '%s - %s',
            $this->startDate->format('d/m/Y'),
            $this->endDate->format('d/m/Y')
        );
    }
}