<?php

declare(strict_types=1);

namespace App\Application\Categories\Handlers;

use App\Application\Categories\Data\CreateCategoryData;
use App\Domain\Categories\Category;
use App\Domain\Categories\CategoryId;
use App\Domain\Categories\CategoryRepositoryInterface;
use App\Domain\Categories\CategoryStatus;
use App\Domain\Categories\CategoryType;

final readonly class CreateCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function handle(CreateCategoryData $data): CategoryId
    {
        $category = new Category(
            id: CategoryId::generate(),
            name: $data->name,
            type: $data->type,
            status: CategoryStatus::ACTIVE,
            color: $data->color,
            icon: $data->icon,
            parentId: $data->parentId,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->categoryRepository->save($category);

        return $category->getId();
    }
}