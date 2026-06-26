<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\FinancialProjections;

use App\Domain\FinancialProjections\PeriodType;
use PHPUnit\Framework\TestCase;

final class PeriodTypeTest extends TestCase
{
    /** @test */
    public function it_has_all_expected_cases(): void
    {
        $cases = PeriodType::cases();
        
        $this->assertCount(3, $cases);
        $this->assertContains(PeriodType::MONTHLY, $cases);
        $this->assertContains(PeriodType::QUARTERLY, $cases);
        $this->assertContains(PeriodType::YEARLY, $cases);
    }

    /** @test */
    public function it_returns_correct_labels(): void
    {
        $this->assertEquals('Mensal', PeriodType::MONTHLY->getLabel());
        $this->assertEquals('Trimestral', PeriodType::QUARTERLY->getLabel());
        $this->assertEquals('Anual', PeriodType::YEARLY->getLabel());
    }

    /** @test */
    public function it_returns_correct_string_values(): void
    {
        $this->assertEquals('monthly', PeriodType::MONTHLY->value);
        $this->assertEquals('quarterly', PeriodType::QUARTERLY->value);
        $this->assertEquals('yearly', PeriodType::YEARLY->value);
    }

    /** @test */
    public function it_can_be_created_from_string(): void
    {
        $this->assertEquals(PeriodType::MONTHLY, PeriodType::from('monthly'));
        $this->assertEquals(PeriodType::QUARTERLY, PeriodType::from('quarterly'));
        $this->assertEquals(PeriodType::YEARLY, PeriodType::from('yearly'));
    }

    /** @test */
    public function it_throws_exception_for_invalid_string(): void
    {
        $this->expectException(\ValueError::class);
        PeriodType::from('invalid');
    }
}