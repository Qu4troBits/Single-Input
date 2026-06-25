<?php

declare(strict_types=1);

namespace App\Application\Categories\Handlers;

use App\Domain\Categories\CategoryId;
use App\Domain\Categories\CategoryRepositoryInterface;
use RuntimeException;

final readonly class DeleteCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function handle(CategoryId $id): void
    {
        $category = $this->categoryRepository->findById($id);

        if ($category === null) {
            throw new RuntimeException('Category not found.');
        }

        $this->categoryRepository->delete($id);
    }
}