<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\FinancialProjections;

use App\Domain\FinancialProjections\ProjectionType;
use PHPUnit\Framework\TestCase;

final class ProjectionTypeTest extends TestCase
{
    /** @test */
    public function it_has_all_expected_cases(): void
    {
        $cases = ProjectionType::cases();
        
        $this->assertCount(5, $cases);
        $this->assertContains(ProjectionType::REVENUE, $cases);
        $this->assertContains(ProjectionType::EXPENSE, $cases);
        $this->assertContains(ProjectionType::PROFIT, $cases);
        $this->assertContains(ProjectionType::CASH_FLOW, $cases);
        $this->assertContains(ProjectionType::BALANCE_SHEET, $cases);
    }

    /** @test */
    public function it_returns_correct_labels(): void
    {
        $this->assertEquals('Receita', ProjectionType::REVENUE->getLabel());
        $this->assertEquals('Despesa', ProjectionType::EXPENSE->getLabel());
        $this->assertEquals('Lucro', ProjectionType::PROFIT->getLabel());
        $this->assertEquals('Fluxo de Caixa', ProjectionType::CASH_FLOW->getLabel());
        $this->assertEquals('Balanço Patrimonial', ProjectionType::BALANCE_SHEET->getLabel());
    }

    /** @test */
    public function it_returns_correct_string_values(): void
    {
        $this->assertEquals('revenue', ProjectionType::REVENUE->value);
        $this->assertEquals('expense', ProjectionType::EXPENSE->value);
        $this->assertEquals('profit', ProjectionType::PROFIT->value);
        $this->assertEquals('cash_flow', ProjectionType::CASH_FLOW->value);
        $this->assertEquals('balance_sheet', ProjectionType::BALANCE_SHEET->value);
    }

    /** @test */
    public function it_can_be_created_from_string(): void
    {
        $this->assertEquals(ProjectionType::REVENUE, ProjectionType::from('revenue'));
        $this->assertEquals(ProjectionType::EXPENSE, ProjectionType::from('expense'));
        $this->assertEquals(ProjectionType::PROFIT, ProjectionType::from('profit'));
        $this->assertEquals(ProjectionType::CASH_FLOW, ProjectionType::from('cash_flow'));
        $this->assertEquals(ProjectionType::BALANCE_SHEET, ProjectionType::from('balance_sheet'));
    }

    /** @test */
    public function it_throws_exception_for_invalid_string(): void
    {
        $this->expectException(\ValueError::class);
        ProjectionType::from('invalid');
    }
}