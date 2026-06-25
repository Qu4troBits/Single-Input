<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Categories\Category;
use App\Domain\Categories\CategoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Tenant\CategoryModel;

final readonly class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function listAll(): array
    {
        $rows = CategoryModel::query()->orderBy('name')->get();
        $items = [];

        foreach ($rows as $row) {
            $items[] = new Category(
                id: (int) $row->getAttribute('id'),
                name: (string) $row->getAttribute('name'),
            );
        }

        return $items;
    }
}
