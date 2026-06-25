<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Categories;

use App\Domain\Categories\Category;
use App\Domain\Categories\CategoryId;
use App\Domain\Categories\CategoryStatus;
use App\Domain\Categories\CategoryType;
use PHPUnit\Framework\TestCase;

final class CategoryTest extends TestCase
{
    public function test_it_creates_category_with_correct_properties(): void
    {
        $id = CategoryId::generate();
        $createdAt = new \DateTimeImmutable('2024-01-01 10:00:00');
        $updatedAt = new \DateTimeImmutable('2024-01-01 10:00:00');

        $category = new Category(
            id: $id,
            name: 'Alimentação',
            type: CategoryType::EXPENSE,
            status: CategoryStatus::ACTIVE,
            color: '#FF0000',
            icon: 'fa-utensils',
            parentId: null,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );

        $this->assertSame($id, $category->getId());
        $this->assertSame('Alimentação', $category->getName());
        $this->assertSame(CategoryType::EXPENSE, $category->getType());
        $this->assertSame(CategoryStatus::ACTIVE, $category->getStatus());
        $this->assertSame('#FF0000', $category->getColor());
        $this->assertSame('fa-utensils', $category->getIcon());
        $this->assertNull($category->getParentId());
        $this->assertSame($createdAt, $category->getCreatedAt());
        $this->assertSame($updatedAt, $category->getUpdatedAt());
    }

    public function test_it_updates_category_properties(): void
    {
        $category = new Category(
            id: CategoryId::generate(),
            name: 'Old Name',
            type: CategoryType::EXPENSE,
            status: CategoryStatus::ACTIVE,
            color: '#FF0000',
            icon: 'fa-utensils',
            parentId: null,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $oldUpdatedAt = $category->getUpdatedAt();

        $category->update(
            name: 'New Name',
            type: CategoryType::INCOME,
            status: CategoryStatus::INACTIVE,
            color: '#00FF00',
            icon: 'fa-money-bill',
            parentId: CategoryId::generate(),
        );

        $this->assertSame('New Name', $category->getName());
        $this->assertSame(CategoryType::INCOME, $category->getType());
        $this->assertSame(CategoryStatus::INACTIVE, $category->getStatus());
        $this->assertSame('#00FF00', $category->getColor());
        $this->assertSame('fa-money-bill', $category->getIcon());
        $this->assertNotNull($category->getParentId());
        $this->assertGreaterThan($oldUpdatedAt, $category->getUpdatedAt());
    }

    public function test_it_changes_category_status(): void
    {
        $category = new Category(
            id: CategoryId::generate(),
            name: 'Test Category',
            type: CategoryType::EXPENSE,
            status: CategoryStatus::ACTIVE,
            color: null,
            icon: null,
            parentId: null,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $category->deactivate();
        $this->assertSame(CategoryStatus::INACTIVE, $category->getStatus());
        $this->assertFalse($category->isActive());

        $category->activate();
        $this->assertSame(CategoryStatus::ACTIVE, $category->getStatus());
        $this->assertTrue($category->isActive());

        $category->archive();
        $this->assertSame(CategoryStatus::ARCHIVED, $category->getStatus());
        $this->assertFalse($category->isActive());
    }

    public function test_it_checks_category_type(): void
    {
        $incomeCategory = new Category(
            id: CategoryId::generate(),
            name: 'Income Category',
            type: CategoryType::INCOME,
            status: CategoryStatus::ACTIVE,
            color: null,
            icon: null,
            parentId: null,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $expenseCategory = new Category(
            id: CategoryId::generate(),
            name: 'Expense Category',
            type: CategoryType::EXPENSE,
            status: CategoryStatus::ACTIVE,
            color: null,
            icon: null,
            parentId: null,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $transferCategory = new Category(
            id: CategoryId::generate(),
            name: 'Transfer Category',
            type: CategoryType::TRANSFER,
            status: CategoryStatus::ACTIVE,
            color: null,
            icon: null,
            parentId: null,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->assertTrue($incomeCategory->isIncome());
        $this->assertFalse($incomeCategory->isExpense());
        $this->assertFalse($incomeCategory->isTransfer());

        $this->assertTrue($expenseCategory->isExpense());
        $this->assertFalse($expenseCategory->isIncome());
        $this->assertFalse($expenseCategory->isTransfer());

        $this->assertTrue($transferCategory->isTransfer());
        $this->assertFalse($transferCategory->isIncome());
        $this->assertFalse($transferCategory->isExpense());
    }

    public function test_it_checks_if_category_has_parent(): void
    {
        $parentId = CategoryId::generate();

        $categoryWithParent = new Category(
            id: CategoryId::generate(),
            name: 'Child Category',
            type: CategoryType::EXPENSE,
            status: CategoryStatus::ACTIVE,
            color: null,
            icon: null,
            parentId: $parentId,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $categoryWithoutParent = new Category(
            id: CategoryId::generate(),
            name: 'Parent Category',
            type: CategoryType::EXPENSE,
            status: CategoryStatus::ACTIVE,
            color: null,
            icon: null,
            parentId: null,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->assertTrue($categoryWithParent->hasParent());
        $this->assertFalse($categoryWithoutParent->hasParent());
    }
}