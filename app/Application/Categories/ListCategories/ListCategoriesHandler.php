<?php

declare(strict_types=1);

namespace App\Application\Categories\ListCategories;

use App\Domain\Categories\Repositories\CategoryRepositoryInterface;

final readonly class ListCategoriesHandler
{
    public function __construct(private CategoryRepositoryInterface $categories)
    {
    }

    public function handle(): array
    {
        return $this->categories->findAll();
    }
}
