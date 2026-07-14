<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\FinancialProjections;

use App\Domain\FinancialProjections\FinancialProjection;
use App\Domain\FinancialProjections\ProjectionItem;
use App\Domain\FinancialProjections\ProjectionPeriod;
use App\Domain\FinancialProjections\ProjectionType;
use App\Domain\Shared\Money;
use PHPUnit\Framework\TestCase;

final class FinancialProjectionTest extends TestCase
{
    private ProjectionPeriod $period;
    private ProjectionType $type;
    private string $title;

    protected function setUp(): void 
    {
        parent::setUp();
        
        $this->period = ProjectionPeriod::createMonthly('2024-01');
        $this->type = ProjectionType::REVENUE;
        $this->title = 'Projeção de Receitas - Janeiro 2024';
    }

    /** @test */
    public function it_can_be_created_with_valid_data(): void
    {
        $projection = new FinancialProjection(
            $this->period,
            $this->type,
            $this->title
        );

        $this->assertEquals($this->period, $projection->getPeriod());
        $this->assertEquals($this->type, $projection->getType());
        $this->assertEquals($this->title, $projection->getTitle());
        $this->assertEmpty($projection->getItems());
        $this->assertNull($projection->getCategoryId());
        $this->assertEquals('base', $projection->getScenario());
    }

    /** @test */
    public function it_can_be_created_with_category_and_scenario(): void
    {
        $categoryId = 'cat-123';
        $scenario = 'optimistic';

        $projection = new FinancialProjection(
            $this->period,
            $this->type,
            $this->title,
            $categoryId,
            $scenario
        );

        $this->assertEquals($categoryId, $projection->getCategoryId());
        $this->assertEquals($scenario, $projection->getScenario());
    }

    /** @test */
    public function it_can_add_items(): void
    {
        $projection = new FinancialProjection(
            $this->period,
            $this->type,
            $this->title
        );

        $item1 = new ProjectionItem(
            'item-1',
            new \DateTimeImmutable('2024-01-15'),
            'Venda produto A',
            Money::of('1000.00'),
            ProjectionType::REVENUE
        );

        $item2 = new ProjectionItem(
            'item-2',
            new \DateTimeImmutable('2024-01-20'),
            'Venda produto B',
            Money::of('1500.00'),
            ProjectionType::REVENUE
        );

        $projection->addItem($item1);
        $projection->addItem($item2);

        $items = $projection->getItems();
        $this->assertCount(2, $items);
        $this->assertEquals($item1, $items[0]);
        $this->assertEquals($item2, $items[1]);
    }

    /** @test */
    public function it_calculates_total_correctly(): void
    {
        $projection = new FinancialProjection(
            $this->period,
            $this->type,
            $this->title
        );

        $item1 = new ProjectionItem(
            'item-1',
            new \DateTimeImmutable('2024-01-15'),
            'Venda produto A',
            Money::of('1000.50'),
            ProjectionType::REVENUE
        );

        $item2 = new ProjectionItem(
            'item-2',
            new \DateTimeImmutable('2024-01-20'),
            'Venda produto B',
            Money::of('1500.75'),
            ProjectionType::REVENUE
        );

        $projection->addItem($item1);
        $projection->addItem($item2);

        $expectedTotal = Money::of('2501.25');
        $this->assertEquals($expectedTotal, $projection->getTotal());
    }

    /** @test */
    public function it_returns_zero_total_when_no_items(): void
    {
        $projection = new FinancialProjection(
            $this->period,
            $this->type,
            $this->title
        );

        $this->assertEquals(Money::zero(), $projection->getTotal());
    }

    /** @test */
    public function it_can_remove_item(): void
    {
        $projection = new FinancialProjection(
            $this->period,
            $this->type,
            $this->title
        );

        $item1 = new ProjectionItem(
            'item-1',
            new \DateTimeImmutable('2024-01-15'),
            'Venda produto A',
            Money::of('1000.00'),
            ProjectionType::REVENUE
        );

        $item2 = new ProjectionItem(
            'item-2',
            new \DateTimeImmutable('2024-01-20'),
            'Venda produto B',
            Money::of('1500.00'),
            ProjectionType::REVENUE
        );

        $projection->addItem($item1);
        $projection->addItem($item2);

        $projection->removeItem('item-1');

        $items = $projection->getItems();
        $this->assertCount(1, $items);
        $this->assertEquals($item2, $items[0]);
    }

    /** @test */
    public function it_does_nothing_when_removing_non_existent_item(): void
    {
        $projection = new FinancialProjection(
            $this->period,
            $this->type,
            $this->title
        );

        $item = new ProjectionItem(
            'item-1',
            new \DateTimeImmutable('2024-01-15'),
            'Venda produto A',
            Money::of('1000.00'),
            ProjectionType::REVENUE
        );

        $projection->addItem($item);

        $projection->removeItem('non-existent');

        $items = $projection->getItems();
        $this->assertCount(1, $items);
        $this->assertEquals($item, $items[0]);
    }

    /** @test */
    public function it_can_update_item(): void
    {
        $projection = new FinancialProjection(
            $this->period,
            $this->type,
            $this->title
        );

        $item = new ProjectionItem(
            'item-1',
            new \DateTimeImmutable('2024-01-15'),
            'Venda produto A',
            Money::of('1000.00'),
            ProjectionType::REVENUE
        );

        $projection->addItem($item);

        $updatedItem = new ProjectionItem(
            'item-1',
            new \DateTimeImmutable('2024-01-15'),
            'Venda produto A atualizada',
            Money::of('1200.00'),
            ProjectionType::REVENUE
        );

        $projection->updateItem($updatedItem);

        $items = $projection->getItems();
        $this->assertCount(1, $items);
        $this->assertEquals('Venda produto A atualizada', $items[0]->getDescription());
        $this->assertEquals(Money::of('1200.00'), $items[0]->getAmount());
    }

    /** @test */
    public function it_does_nothing_when_updating_non_existent_item(): void
    {
        $projection = new FinancialProjection(
            $this->period,
            $this->type,
            $this->title
        );

        $item = new ProjectionItem(
            'item-1',
            new \DateTimeImmutable('2024-01-15'),
            'Venda produto A',
            Money::of('1000.00'),
            ProjectionType::REVENUE
        );

        $projection->addItem($item);

        $nonExistentItem = new ProjectionItem(
            'non-existent',
            new \DateTimeImmutable('2024-01-15'),
            'Item não existente',
            Money::of('500.00'),
            ProjectionType::REVENUE
        );

        $projection->updateItem($nonExistentItem);

        $items = $projection->getItems();
        $this->assertCount(1, $items);
        $this->assertEquals($item, $items[0]);
    }

    /** @test */
    public function it_can_get_item_by_id(): void
    {
        $projection = new FinancialProjection(
            $this->period,
            $this->type,
            $this->title
        );

        $item1 = new ProjectionItem(
            'item-1',
            new \DateTimeImmutable('2024-01-15'),
            'Venda produto A',
            Money::of('1000.00'),
            ProjectionType::REVENUE
        );

        $item2 = new ProjectionItem(
            'item-2',
            new \DateTimeImmutable('2024-01-20'),
            'Venda produto B',
            Money::of('1500.00'),
            ProjectionType::REVENUE
        );

        $projection->addItem($item1);
        $projection->addItem($item2);

        $foundItem = $projection->getItem('item-2');
        $this->assertEquals($item2, $foundItem);

        $nonExistentItem = $projection->getItem('non-existent');
        $this->assertNull($nonExistentItem);
    }

    /** @test */
    public function it_can_get_items_by_category(): void
    {
        $projection = new FinancialProjection(
            $this->period,
            $this->type,
            $this->title
        );

        $item1 = new ProjectionItem(
            'item-1',
            new \DateTimeImmutable('2024-01-15'),
            'Venda produto A',
            Money::of('1000.00'),
            ProjectionType::REVENUE,
            'cat-1',
            'Produtos'
        );

        $item2 = new ProjectionItem(
            'item-2',
            new \DateTimeImmutable('2024-01-20'),
            'Venda produto B',
            Money::of('1500.00'),
            ProjectionType::REVENUE,
            'cat-2',
            'Serviços'
        );

        $item3 = new ProjectionItem(
            'item-3',
            new \DateTimeImmutable('2024-01-25'),
            'Venda produto C',
            Money::of('2000.00'),
            ProjectionType::REVENUE,
            'cat-1',
            'Produtos'
        );

        $projection->addItem($item1);
        $projection->addItem($item2);
        $projection->addItem($item3);

        $categoryItems = $projection->getItemsByCategory('cat-1');
        $this->assertCount(2, $categoryItems);
        $this->assertEquals($item1, $categoryItems[0]);
        $this->assertEquals($item3, $categoryItems[1]);

        $emptyCategoryItems = $projection->getItemsByCategory('non-existent');
        $this->assertEmpty($emptyCategoryItems);
    }

    /** @test */
    public function it_can_get_items_by_date_range(): void
    {
        $projection = new FinancialProjection(
            $this->period,
            $this->type,
            $this->title
        );

        $item1 = new ProjectionItem(
            'item-1',
            new \DateTimeImmutable('2024-01-10'),
            'Venda produto A',
            Money::of('1000.00'),
            ProjectionType::REVENUE
        );

        $item2 = new ProjectionItem(
            'item-2',
            new \DateTimeImmutable('2024-01-15'),
            'Venda produto B',
            Money::of('1500.00'),
            ProjectionType::REVENUE
        );

        $item3 = new ProjectionItem(
            'item-3',
            new \DateTimeImmutable('2024-01-20'),
            'Venda produto C',
            Money::of('2000.00'),
            ProjectionType::REVENUE
        );

        $projection->addItem($item1);
        $projection->addItem($item2);
        $projection->addItem($item3);

        $startDate = new \DateTimeImmutable('2024-01-12');
        $endDate = new \DateTimeImmutable('2024-01-18');

        $rangeItems = $projection->getItemsByDateRange($startDate, $endDate);
        $this->assertCount(1, $rangeItems);
        $this->assertEquals($item2, $rangeItems[0]);
    }
}