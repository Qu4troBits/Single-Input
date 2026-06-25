<?php

declare(strict_types=1);

namespace App\Domain\Categories;

interface CategoryRepositoryInterface
{
    public function save(Category $category): void;

    public function findById(CategoryId $id): ?Category;

    /** @return array<Category> */
    public function findAll(): array;

    /** @return array<Category> */
    public function findByType(CategoryType $type): array;

    /** @return array<Category> */
    public function findByStatus(CategoryStatus $status): array;

    /** @return array<Category> */
    public function findByParentId(?CategoryId $parentId): array;

    /** @return array<Category> */
    public function findActiveByType(CategoryType $type): array;

    public function delete(CategoryId $id): void;
}