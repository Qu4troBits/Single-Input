<?php

declare(strict_types=1);

namespace App\Domain\Categories;

interface CategoryRepositoryInterface
{
    /**
     * @return list<Category>
     */
    public function listAll(): array;
}
