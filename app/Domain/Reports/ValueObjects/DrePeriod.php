<?php

declare(strict_types=1);

namespace App\Domain\Reports\ValueObjects;

final readonly class DrePeriod
{
    public function __construct(
        private \DateTimeImmutable $startDate,
        private \DateTimeImmutable $endDate,
        private string $periodType,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->endDate < $this->startDate) {
            throw new \InvalidArgumentException('End date must be after start date.');
        }

        $validPeriodTypes = ['monthly', 'quarterly', 'yearly', 'custom'];
        if (!in_array($this->periodType, $validPeriodTypes, true)) {
            throw new \InvalidArgumentException('Invalid period type. Must be one of: ' . implode(', ', $validPeriodTypes));
        }

        if ($this->periodType === 'monthly') {
            $startMonth = $this->startDate->format('Y-m');
            $endMonth = $this->endDate->format('Y-m');
            if ($startMonth !== $endMonth) {
                throw new \InvalidArgumentException('Monthly period must start and end in the same month.');
            }
        }

        if ($this->periodType === 'quarterly') {
            $startQuarter = $this->getQuarter($this->startDate);
            $endQuarter = $this->getQuarter($this->endDate);
            if ($startQuarter !== $endQuarter) {
                throw new \InvalidArgumentException('Quarterly period must start and end in the same quarter.');
            }
        }

        if ($this->periodType === 'yearly') {
            $startYear = $this->startDate->format('Y');
            $endYear = $this->endDate->format('Y');
            if ($startYear !== $endYear) {
                throw new \InvalidArgumentException('Yearly period must start and end in the same year.');
            }
        }
    }

    private function getQuarter(\DateTimeImmutable $date): int
    {
        $month = (int) $date->format('n');
        return (int) ceil($month / 3);
    }

    public static function createMonthly(string $yearMonth): self
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m', $yearMonth);
        if ($date === false) {
            throw new \InvalidArgumentException('Invalid year-month format. Use YYYY-MM.');
        }

        $startDate = $date->modify('first day of this month')->setTime(0, 0, 0);
        $endDate = $date->modify('last day of this month')->setTime(23, 59, 59);

        return new self($startDate, $endDate, 'monthly');
    }

    public static function createQuarterly(string $year, int $quarter): self
    {
        if ($quarter < 1 || $quarter > 4) {
            throw new \InvalidArgumentException('Quarter must be between 1 and 4.');
        }

        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth = $quarter * 3;

        $startDate = \DateTimeImmutable::createFromFormat('Y-m-d', "$year-$startMonth-01")
            ->setTime(0, 0, 0);
        $endDate = $startDate->modify("last day of +2 months")->setTime(23, 59, 59);

        return new self($startDate, $endDate, 'quarterly');
    }

    public static function createYearly(string $year): self
    {
        $startDate = \DateTimeImmutable::createFromFormat('Y-m-d', "$year-01-01")
            ->setTime(0, 0, 0);
        $endDate = \DateTimeImmutable::createFromFormat('Y-m-d', "$year-12-31")
            ->setTime(23, 59, 59);

        return new self($startDate, $endDate, 'yearly');
    }

    public static function createCustom(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): self
    {
        return new self($startDate, $endDate, 'custom');
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getPeriodType(): string
    {
        return $this->periodType;
    }

    public function getLabel(): string
    {
        return match ($this->periodType) {
            'monthly' => $this->startDate->format('F Y'),
            'quarterly' => $this->getQuarter($this->startDate) . 'º Trimestre de ' . $this->startDate->format('Y'),
            'yearly' => 'Ano ' . $this->startDate->format('Y'),
            'custom' => $this->startDate->format('d/m/Y') . ' a ' . $this->endDate->format('d/m/Y'),
            default => $this->periodType,
        };
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

    public function toArray(): array
    {
        return [
            'start_date' => $this->startDate->format('Y-m-d'),
            'end_date' => $this->endDate->format('Y-m-d'),
            'period_type' => $this->periodType,
            'label' => $this->getLabel(),
            'duration_in_days' => $this->getDurationInDays(),
        ];
    }

    public function toString(): string
    {
        return $this->getLabel();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function getFormattedPeriod(): string
    {
        return $this->getLabel();
    }

    public function getDaysInPeriod(): int
    {
        return $this->getDurationInDays();
    }

    public function isWithinPeriod(\DateTimeImmutable $date): bool
    {
        return $this->contains($date);
    }

    public function getPreviousPeriod(): self
    {
        $startDate = $this->startDate->modify('first day of previous month');
        return new self($startDate, $startDate->modify('last day of this month'), $this->periodType);
    }

    public function getNextPeriod(): self
    {
        $startDate = $this->startDate->modify('first day of next month');
        return new self($startDate, $startDate->modify('last day of this month'), $this->periodType);
    }

    public function getQuarterlyPeriod(): int
    {
        return $this->getQuarter($this->startDate);
    }

    public function getYearlyPeriod(): int
    {
        return (int) $this->startDate->format('Y');
    }

    public function getMonthName(): string
    {
        $months = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro',
        ];
        return $months[(int) $this->startDate->format('n')] ?? '';
    }

    public function getQuarterName(): string
    {
        return $this->getQuarter($this->startDate) . 'º Trimestre';
    }
}