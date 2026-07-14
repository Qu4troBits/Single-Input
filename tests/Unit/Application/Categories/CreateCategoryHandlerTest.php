<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Categories;

use App\Application\Categories\DTOs\CreateCategoryData;
use App\Application\Categories\Handlers\CreateCategoryHandler;
use App\Domain\Categories\ValueObjects\CategoryId;
use App\Domain\Categories\Repositories\CategoryRepositoryInterface;
use App\Domain\Categories\ValueObjects\CategoryType;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class CreateCategoryHandlerTest extends TestCase
{
    private CategoryRepositoryInterface&MockObject $repository;
    private CreateCategoryHandler $handler;
 
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(CategoryRepositoryInterface::class);
        $this->handler = new CreateCategoryHandler($this->repository);
    }

    public function test_it_creates_category(): void
    {
        $data = new CreateCategoryData(
            name: 'Alimentação',
            type: CategoryType::EXPENSE,
            code: 'ALIMENTACAO',
            description: 'Categorias de gastos relacionados a alimentação',
            color: '#FF0000',
            icon: 'fa-utensils',
            parentId: null,
        );

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($category) use ($data) {
                return $category->getName() === $data->name
                    && $category->getType() === $data->type
                    && $category->getColor() === $data->color
                    && $category->getIcon() === $data->icon
                    && $category->getParentId() === null;
            }));

        $categoryId = $this->handler->handle($data);

        $this->assertNotNull($categoryId);
    }

    public function test_it_creates_category_with_parent(): void
    {
        $parentId = CategoryId::generate();
        $parentCategory = new \App\Domain\Categories\Entities\Category(
            id: $parentId,
            name: 'Parent Category',
            type: CategoryType::EXPENSE,
            code: 'PARENT',
            description: null,
            color: null,
            icon: null,
            isOperating: true,
            isTaxDeductible: false,
            includeInReports: true,
            isDefault: false,
            parentId: null,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $data = new CreateCategoryData(
            name: 'Restaurante',
            type: CategoryType::EXPENSE,
            code: 'RESTAURANTE',
            description: 'Categorias de gastos relacionados a restaurante',
            color: '#FF5733',
            icon: 'fa-hamburger',
            parentId: $parentId,
        );

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with($parentId)
            ->willReturn($parentCategory);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($category) use ($data, $parentId) {
                return $category->getName() === $data->name
                    && $category->getType() === $data->type
                    && $category->getColor() === $data->color
                    && $category->getIcon() === $data->icon
                    && $category->getParentId() !== null
                    && $category->getParentId()->equals($parentId);
            }));

        $categoryId = $this->handler->handle($data);

        $this->assertNotNull($categoryId);
    }

    public function test_it_creates_category_with_nullable_fields(): void
    {
        $data = new CreateCategoryData(
            name: 'Salário',
            type: CategoryType::TRANSFER,
            code: 'SALARIO',
            description: 'Categorias de receitas relacionadas a salário',
            color: null,
            icon: null,
            parentId: null,
        );

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($category) use ($data) {
                return $category->getName() === $data->name
                    && $category->getType() === $data->type
                    && $category->getColor() === null
                    && $category->getIcon() === null
                    && $category->getParentId() === null;
            }));

        $categoryId = $this->handler->handle($data);

        $this->assertNotNull($categoryId);
    }

    public function test_it_creates_category_with_different_types(): void
    {
        $types = [
            CategoryType::REVENUE,
            CategoryType::EXPENSE,
            CategoryType::TRANSFER,
        ];

        $this->repository
            ->expects($this->exactly(count($types)))
            ->method('save')
            ->with($this->callback(function ($category) use ($types) {
                return in_array($category->getType(), $types, true);
            }));

        foreach ($types as $type) {
            $data = new CreateCategoryData(
                name: "Category {$type->value}",
                type: $type,
                code: "CATEGORY_{$type->value}",
                description: "Categorias de {$type->value} relacionadas",
                color: null,
                icon: null,
                parentId: null,
            );

            $categoryId = $this->handler->handle($data);

            $this->assertNotNull($categoryId);
        }
    }
}