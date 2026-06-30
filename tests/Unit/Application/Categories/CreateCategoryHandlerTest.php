<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Categories;

use App\Application\Categories\DTOs\CreateCategoryData;
use App\Application\Categories\Handlers\CreateCategoryHandler;
use App\Domain\Categories\CategoryId;
use App\Domain\Categories\Repositories\CategoryRepositoryInterface;
use App\Domain\Categories\CategoryType;
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

        $data = new CreateCategoryData(
            name: 'Restaurante',
            type: CategoryType::EXPENSE,
            color: '#FF5733',
            icon: 'fa-hamburger',
            parentId: $parentId,
        );

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($category) use ($data, $parentId) {
                return $category->getName() === $data->name
                    && $category->getType() === $data->type
                    && $category->getColor() === $data->color
                    && $category->getIcon() === $data->icon
                    && $category->getParentId()->equals($parentId);
            }));

        $categoryId = $this->handler->handle($data);

        $this->assertNotNull($categoryId);
    }

    public function test_it_creates_category_with_nullable_fields(): void
    {
        $data = new CreateCategoryData(
            name: 'Salário',
            type: CategoryType::INCOME,
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
            CategoryType::INCOME,
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
                color: null,
                icon: null,
                parentId: null,
            );

            $categoryId = $this->handler->handle($data);

            $this->assertNotNull($categoryId);
        }
    }
}