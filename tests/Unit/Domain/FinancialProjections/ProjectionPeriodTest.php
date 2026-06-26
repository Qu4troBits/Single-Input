<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\FinancialProjections;

use App\Domain\FinancialProjections\PeriodType;
use App\Domain\FinancialProjections\ProjectionPeriod;
use PHPUnit\Framework\TestCase;

final class ProjectionPeriodTest extends TestCase
{
    /** @test */
    public function it_can_be_created_with_valid_dates(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');
        $periodType = PeriodType::MONTHLY;

        $period = new ProjectionPeriod($startDate, $endDate, $periodType);

        $this->assertEquals($startDate, $period->getStartDate());
        $this->assertEquals($endDate, $period->getEndDate());
        $this->assertEquals($periodType, $period->getPeriodType());
    }

    /** @test */
    public function it_throws_exception_when_end_date_is_before_start_date(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('End date must be after start date.');

        $startDate = new \DateTimeImmutable('2024-01-31');
        $endDate = new \DateTimeImmutable('2024-01-01');
        $periodType = PeriodType::MONTHLY;

        new ProjectionPeriod($startDate, $endDate, $periodType);
    }

    /** @test */
    public function it_can_create_monthly_period_from_year_month_string(): void
    {
        $period = ProjectionPeriod::createMonthly('2024-01');

        $this->assertEquals('2024-01-01', $period->getStartDate()->format('Y-m-d'));
        $this->assertEquals('2024-01-31', $period->getEndDate()->format('Y-m-d'));
        $this->assertEquals(PeriodType::MONTHLY, $period->getPeriodType());
    }

    /** @test */
    public function it_throws_exception_for_invalid_year_month_format(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid year-month format. Use YYYY-MM.');

        ProjectionPeriod::createMonthly('2024/01');
    }

    /** @test */
    public function it_returns_correct_label_for_monthly_period(): void
    {
        $period = ProjectionPeriod::createMonthly('2024-01');
        $this->assertEquals('Janeiro de 2024', $period->getLabel());
    }

    /** @test */
    public function it_returns_correct_label_for_quarterly_period(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-03-31');
        $period = new ProjectionPeriod($startDate, $endDate, PeriodType::QUARTERLY);

        $this->assertEquals('1º Trimestre de 2024', $period->getLabel());
    }

    /** @test */
    public function it_returns_correct_label_for_yearly_period(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-12-31');
        $period = new ProjectionPeriod($startDate, $endDate, PeriodType::YEARLY);

        $this->assertEquals('Ano 2024', $period->getLabel());
    }

    /** @test */
    public function it_calculates_duration_in_days(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');
        $period = new ProjectionPeriod($startDate, $endDate, PeriodType::MONTHLY);

        $this->assertEquals(31, $period->getDurationInDays());
    }

    /** @test */
    public function it_can_check_if_date_is_within_period(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');
        $period = new ProjectionPeriod($startDate, $endDate, PeriodType::MONTHLY);

        $dateWithin = new \DateTimeImmutable('2024-01-15');
        $dateBefore = new \DateTimeImmutable('2023-12-31');
        $dateAfter = new \DateTimeImmutable('2024-02-01');

        $this->assertTrue($period->contains($dateWithin));
        $this->assertFalse($period->contains($dateBefore));
        $this->assertFalse($period->contains($dateAfter));
    }
}