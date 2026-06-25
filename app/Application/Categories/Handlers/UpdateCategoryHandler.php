<?php

declare(strict_types=1);

namespace App\Application\Categories\Handlers;

use App\Application\Categories\Data\UpdateCategoryData;
use App\Domain\Categories\CategoryId;
use App\Domain\Categories\CategoryRepositoryInterface;
use RuntimeException;

final readonly class UpdateCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function handle(CategoryId $id, UpdateCategoryData $data): void
    {
        $category = $this->categoryRepository->findById($id);

        if ($category === null) {
            throw new RuntimeException('Category not found.');
        }

        $category->update(
            name: $data->name,
            type: $data->type,
            status: $data->status,
            color: $data->color,
            icon: $data->icon,
            parentId: $data->parentId,
        );

        $this->categoryRepository->save($category);
    }
}