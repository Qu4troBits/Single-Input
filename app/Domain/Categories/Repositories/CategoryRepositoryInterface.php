<?php

declare(strict_types=1);

namespace App\Domain\Categories\Repositories;

use App\Domain\Categories\Entities\Category;
use App\Domain\Categories\ValueObjects\CategoryId;
use App\Domain\Categories\ValueObjects\CategoryType;

interface CategoryRepositoryInterface
{
    public function findById(CategoryId $id): ?Category;

    public function findByCode(string $code): ?Category;

    public function findByName(string $name): ?Category;

    public function findAll(
        ?CategoryType $type = null,
        ?CategoryId $parentId = null,
        bool $isOperating = false,
        bool $isTaxDeductible = false,
        bool $includeInReports = false,
        bool $isDefault = false,
        int $page = 1,
        int $perPage = 20
    ): array;

    public function findAllByType(CategoryType $type): array;

    public function findAllRevenue(): array;

    public function findAllExpense(): array;

    public function findAllTransfer(): array;

    public function findAllRoot(): array;

    public function findAllChildren(CategoryId $parentId): array;

    public function findTree(?CategoryType $type = null): array;

    public function save(Category $category): void;

    public function delete(Category $category): void;

    public function existsWithCode(string $code, ?CategoryId $excludeId = null): bool;

    public function existsWithName(string $name, ?CategoryId $excludeId = null): bool;

    public function countByType(CategoryType $type): int;

    public function countByParent(CategoryId $parentId): int;

    public function getDefaultCategories(): array;
}
