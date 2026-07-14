<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\FinancialProjections;

use App\Domain\FinancialProjections\ProjectionItem;
use App\Domain\FinancialProjections\ProjectionType;
use App\Domain\Shared\Money;
use PHPUnit\Framework\TestCase;

final class ProjectionItemTest extends TestCase
{
    /** @test */
    public function it_can_be_created_with_valid_data(): void
    {
        $id = 'item-123';
        $date = new \DateTimeImmutable('2024-01-15'); 
        $description = 'Venda de produto X';
        $amount = Money::of('1000.50');
        $type = ProjectionType::REVENUE;
        $categoryId = 'cat-456';
        $categoryName = 'Produtos';
        $notes = 'Venda realizada para cliente Y';
        $source = 'manual';

        $item = new ProjectionItem(
            $id,
            $date,
            $description,
            $amount,
            $type,
            $categoryId,
            $categoryName,
            $notes,
            $source
        );

        $this->assertEquals($id, $item->getId());
        $this->assertEquals($date, $item->getDate());
        $this->assertEquals($description, $item->getDescription());
        $this->assertEquals($amount, $item->getAmount());
        $this->assertEquals($type, $item->getType());
        $this->assertEquals($categoryId, $item->getCategoryId());
        $this->assertEquals($categoryName, $item->getCategoryName());
        $this->assertEquals($notes, $item->getNotes());
        $this->assertEquals($source, $item->getSource());
    }

    /** @test */
    public function it_throws_exception_for_empty_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ID cannot be empty.');

        new ProjectionItem(
            '',
            new \DateTimeImmutable('2024-01-15'),
            'Description',
            Money::of('1000.50'),
            ProjectionType::REVENUE
        );
    }

    /** @test */
    public function it_throws_exception_for_empty_description(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Description cannot be empty.');

        new ProjectionItem(
            'item-123',
            new \DateTimeImmutable('2024-01-15'),
            '',
            Money::of('1000.50'),
            ProjectionType::REVENUE
        );
    }

    /** @test */
    public function it_throws_exception_for_description_too_long(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Description cannot exceed 255 characters.');

        $longDescription = str_repeat('a', 256);

        new ProjectionItem(
            'item-123',
            new \DateTimeImmutable('2024-01-15'),
            $longDescription,
            Money::of('1000.50'),
            ProjectionType::REVENUE
        );
    }

    /** @test */
    public function it_throws_exception_for_negative_amount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount cannot be negative.');

        new ProjectionItem(
            'item-123',
            new \DateTimeImmutable('2024-01-15'),
            'Description',
            Money::of('-1000.50'),
            ProjectionType::REVENUE
        );
    }

    /** @test */
    public function it_throws_exception_for_category_name_too_long(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Category name cannot exceed 100 characters.');

        $longCategoryName = str_repeat('a', 101);

        new ProjectionItem(
            'item-123',
            new \DateTimeImmutable('2024-01-15'),
            'Description',
            Money::of('1000.50'),
            ProjectionType::REVENUE,
            'cat-456',
            $longCategoryName
        );
    }

    /** @test */
    public function it_throws_exception_for_source_too_long(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Source cannot exceed 50 characters.');

        $longSource = str_repeat('a', 51);

        new ProjectionItem(
            'item-123',
            new \DateTimeImmutable('2024-01-15'),
            'Description',
            Money::of('1000.50'),
            ProjectionType::REVENUE,
            null,
            null,
            null,
            $longSource
        );
    }

    /** @test */
    public function it_can_be_created_with_minimal_data(): void
    {
        $item = new ProjectionItem(
            'item-123',
            new \DateTimeImmutable('2024-01-15'),
            'Description',
            Money::of('1000.50'),
            ProjectionType::REVENUE
        );

        $this->assertNull($item->getCategoryId());
        $this->assertNull($item->getCategoryName());
        $this->assertNull($item->getNotes());
        $this->assertNull($item->getSource());
    }

    /** @test */
    public function it_can_update_notes(): void
    {
        $item = new ProjectionItem(
            'item-123',
            new \DateTimeImmutable('2024-01-15'),
            'Description',
            Money::of('1000.50'),
            ProjectionType::REVENUE,
            null,
            null,
            'Initial notes'
        );

        $this->assertEquals('Initial notes', $item->getNotes());
    }

    /** @test */
    public function it_can_update_source(): void
    {
        $item = new ProjectionItem(
            'item-123',
            new \DateTimeImmutable('2024-01-15'),
            'Description',
            Money::of('1000.50'),
            ProjectionType::REVENUE,
            null,
            null,
            null,
            'import'
        );

        $this->assertEquals('import', $item->getSource());
    }

    /** @test */
    public function it_returns_formatted_date(): void
    {
        $item = new ProjectionItem(
            'item-123',
            new \DateTimeImmutable('2024-01-15'),
            'Description',
            Money::of('1000.50'),
            ProjectionType::REVENUE
        );

        $this->assertEquals('15/01/2024', $item->getFormattedDate());
    }

    /** @test */
    public function it_returns_formatted_amount(): void
    {
        $item = new ProjectionItem(
            'item-123',
            new \DateTimeImmutable('2024-01-15'),
            'Description',
            Money::of('1000.50'),
            ProjectionType::REVENUE
        );

        $this->assertEquals('R$ 1.000,50', $item->getFormattedAmount());
    }
}