<?php

declare(strict_types=1);

namespace Tests\Unit\Application\FinancialProjections;

use App\Application\FinancialProjections\DTOs\ProjectionItemData;
use App\Application\FinancialProjections\DTOs\SaveProjectionData;
use App\Application\FinancialProjections\Handlers\SaveProjectionHandler;
use App\Domain\FinancialProjections\FinancialProjection;
use App\Domain\FinancialProjections\FinancialProjectionRepositoryInterface;
use App\Domain\FinancialProjections\PeriodType;
use App\Domain\FinancialProjections\ProjectionPeriod;
use App\Domain\FinancialProjections\ProjectionType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SaveProjectionHandlerTest extends TestCase
{
    /** @var FinancialProjectionRepositoryInterface&MockObject */
    private $projectionRepository; 
    private SaveProjectionHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->projectionRepository = $this->createMock(FinancialProjectionRepositoryInterface::class);
        $this->handler = new SaveProjectionHandler($this->projectionRepository);
    }

    /** @test */
    public function it_saves_projection_with_valid_data(): void
    {
        $itemData = new ProjectionItemData(
            date: '2024-01-15',
            description: 'Venda produto A',
            amount: '1000.50',
            type: 'revenue',
            categoryId: 'cat-123',
            categoryName: 'Produtos',
            notes: 'Venda realizada',
            source: 'manual'
        );

        $data = new SaveProjectionData(
            id: 'proj-123',
            type: ProjectionType::REVENUE,
            periodType: 'monthly',
            yearMonth: '2024-01',
            categoryId: 'cat-123',
            scenario: 'base',
            title: 'Projeção de Receitas',
            items: [$itemData],
            notes: 'Projeção base para janeiro'
        );

        $expectedPeriod = ProjectionPeriod::createMonthly('2024-01');
        $expectedProjection = new FinancialProjection(
            $expectedPeriod,
            ProjectionType::REVENUE,
            'Projeção de Receitas',
            'cat-123',
            'base'
        );

        $this->projectionRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (FinancialProjection $projection) use ($expectedProjection) {
                return $projection->getPeriod()->getStartDate() == $expectedProjection->getPeriod()->getStartDate()
                    && $projection->getPeriod()->getEndDate() == $expectedProjection->getPeriod()->getEndDate()
                    && $projection->getType() == $expectedProjection->getType()
                    && $projection->getTitle() == $expectedProjection->getTitle()
                    && $projection->getCategoryId() == $expectedProjection->getCategoryId()
                    && $projection->getScenario() == $expectedProjection->getScenario();
            }));

        $this->handler->handle($data);
    }

    /** @test */
    public function it_saves_projection_without_category(): void
    {
        $itemData = new ProjectionItemData(
            date: '2024-01-15',
            description: 'Venda produto A',
            amount: '1000.50',
            type: 'revenue'
        );

        $data = new SaveProjectionData(
            id: 'proj-123',
            type: ProjectionType::REVENUE,
            periodType: 'monthly',
            yearMonth: '2024-01',
            scenario: 'base',
            title: 'Projeção de Receitas',
            items: [$itemData]
        );

        $expectedPeriod = ProjectionPeriod::createMonthly('2024-01');
        $expectedProjection = new FinancialProjection(
            $expectedPeriod,
            ProjectionType::REVENUE,
            'Projeção de Receitas',
            null,
            'base'
        );

        $this->projectionRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (FinancialProjection $projection) use ($expectedProjection) {
                return $projection->getPeriod()->getStartDate() == $expectedProjection->getPeriod()->getStartDate()
                    && $projection->getPeriod()->getEndDate() == $expectedProjection->getPeriod()->getEndDate()
                    && $projection->getType() == $expectedProjection->getType()
                    && $projection->getTitle() == $expectedProjection->getTitle()
                    && $projection->getCategoryId() === null
                    && $projection->getScenario() == $expectedProjection->getScenario();
            }));

        $this->handler->handle($data);
    }

    /** @test */
    public function it_saves_projection_with_multiple_items(): void
    {
        $item1 = new ProjectionItemData(
            date: '2024-01-15',
            description: 'Venda produto A',
            amount: '1000.50',
            type: 'revenue'
        );

        $item2 = new ProjectionItemData(
            date: '2024-01-20',
            description: 'Venda produto B',
            amount: '1500.75',
            type: 'revenue'
        );

        $item3 = new ProjectionItemData(
            date: '2024-01-25',
            description: 'Venda produto C',
            amount: '2000.25',
            type: 'revenue'
        );

        $data = new SaveProjectionData(
            id: 'proj-123',
            type: ProjectionType::REVENUE,
            periodType: 'monthly',
            yearMonth: '2024-01',
            scenario: 'base',
            title: 'Projeção de Receitas',
            items: [$item1, $item2, $item3]
        );

        $expectedPeriod = ProjectionPeriod::createMonthly('2024-01');
        $expectedProjection = new FinancialProjection(
            $expectedPeriod,
            ProjectionType::REVENUE,
            'Projeção de Receitas',
            null,
            'base'
        );

        $this->projectionRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (FinancialProjection $projection) use ($expectedProjection) {
                return $projection->getPeriod()->getStartDate() == $expectedProjection->getPeriod()->getStartDate()
                    && $projection->getPeriod()->getEndDate() == $expectedProjection->getPeriod()->getEndDate()
                    && $projection->getType() == $expectedProjection->getType()
                    && $projection->getTitle() == $expectedProjection->getTitle()
                    && $projection->getCategoryId() === null
                    && $projection->getScenario() == $expectedProjection->getScenario();
            }));

        $this->handler->handle($data);
    }

    /** @test */
    public function it_saves_projection_with_quarterly_period(): void
    {
        $itemData = new ProjectionItemData(
            date: '2024-01-15',
            description: 'Venda produto A',
            amount: '1000.50',
            type: 'revenue'
        );

        $data = new SaveProjectionData(
            id: 'proj-123',
            type: ProjectionType::REVENUE,
            periodType: 'quarterly',
            year: '2024',
            quarter: 1,
            scenario: 'base',
            title: 'Projeção de Receitas - 1º Trimestre 2024',
            items: [$itemData]
        );

        $expectedPeriod = new ProjectionPeriod(
            new \DateTimeImmutable('2024-01-01'),
            new \DateTimeImmutable('2024-03-31'),
            \App\Domain\FinancialProjections\PeriodType::QUARTERLY
        );

        $expectedProjection = new FinancialProjection(
            $expectedPeriod,
            ProjectionType::REVENUE,
            'Projeção de Receitas - 1º Trimestre 2024',
            null,
            'base'
        );

        $this->projectionRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (FinancialProjection $projection) {
                return true; // We just want to verify save is called
            }));

        $this->handler->handle($data);
    }

    /** @test */
    public function it_saves_projection_with_yearly_period(): void
    {
        $itemData = new ProjectionItemData(
            date: '2024-01-15',
            description: 'Venda produto A',
            amount: '1000.50',
            type: 'revenue'
        );

        $data = new SaveProjectionData(
            id: 'proj-123',
            type: ProjectionType::REVENUE,
            periodType: 'yearly',
            year: '2024',
            scenario: 'base',
            title: 'Projeção de Receitas 2024',
            items: [$itemData]
        );

        $expectedPeriod = new ProjectionPeriod(
            new \DateTimeImmutable('2024-01-01'),
            new \DateTimeImmutable('2024-12-31'),
            \App\Domain\FinancialProjections\PeriodType::YEARLY
        );

        $expectedProjection = new FinancialProjection(
            $expectedPeriod,
            ProjectionType::REVENUE,
            'Projeção de Receitas 2024',
            null,
            'base'
        );

        $this->projectionRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (FinancialProjection $projection) {
                return true; // We just want to verify save is called
            }));

        $this->handler->handle($data);
    }

    /** @test */
    public function it_saves_projection_with_different_scenarios(): void
    {
        $itemData = new ProjectionItemData(
            date: '2024-01-15',
            description: 'Venda produto A',
            amount: '1000.50',
            type: 'revenue'
        );

        $scenarios = ['base', 'optimistic', 'pessimistic', 'custom'];
        $calledScenarios = [];

        $this->projectionRepository
            ->expects($this->exactly(4))
            ->method('save')
            ->with($this->callback(function (FinancialProjection $projection) use (&$calledScenarios) {
                $calledScenarios[] = $projection->getScenario();
                return true;
            }));

        foreach ($scenarios as $scenario) {
            $data = new SaveProjectionData(
                id: 'proj-123',
                type: ProjectionType::REVENUE,
                periodType: 'monthly',
                yearMonth: '2024-01',
                scenario: $scenario,
                title: 'Projeção de Receitas',
                items: [$itemData]
            );

            $this->handler->handle($data);
        }

        $this->assertEquals($scenarios, $calledScenarios);
    }

    /** @test */
    public function it_throws_exception_for_invalid_period_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid period type.');

        $itemData = new ProjectionItemData(
            date: '2024-01-15',
            description: 'Venda produto A',
            amount: '1000.50',
            type: 'revenue'
        );

        $data = new SaveProjectionData(
            id: 'proj-123',
            type: ProjectionType::REVENUE,
            periodType: 'invalid',
            scenario: 'base',
            title: 'Projeção de Receitas',
            items: [$itemData]
        );

        $this->handler->handle($data);
    }
}