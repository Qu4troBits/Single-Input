<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Categories\Entities\Category;
use App\Domain\Categories\Repositories\CategoryRepositoryInterface;
use App\Domain\Categories\ValueObjects\CategoryId;
use App\Domain\Categories\ValueObjects\CategoryType;
use App\Infrastructure\Persistence\Eloquent\Models\CategoryModel;
use DateTimeImmutable;

final class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function findById(CategoryId $id): ?Category
    {
        $model = CategoryModel::find($id->toString());

        if (!$model) {
            return null;
        }

        return $this->mapToEntity($model);
    }

    public function findByCode(string $code): ?Category
    {
        $model = CategoryModel::where('code', $code)->first();

        if (!$model) {
            return null;
        }

        return $this->mapToEntity($model);
    }

    public function findByName(string $name): ?Category
    {
        $model = CategoryModel::where('name', $name)->first();

        if (!$model) {
            return null;
        }

        return $this->mapToEntity($model);
    }

    public function findAll(
        ?CategoryType $type = null,
        ?CategoryId $parentId = null,
        bool $isOperating = false,
        bool $isTaxDeductible = false,
        bool $includeInReports = false,
        bool $isDefault = false,
        int $page = 1,
        int $perPage = 20
    ): array {
        $query = CategoryModel::query();

        if ($type) {
            $query->where('type', $type->value);
        }

        if ($parentId) {
            $query->where('parent_id', $parentId->toString());
        } else {
            $query->whereNull('parent_id');
        }

        if ($isOperating) {
            $query->where('is_operating', true);
        }

        if ($isTaxDeductible) {
            $query->where('is_tax_deductible', true);
        }

        if ($includeInReports) {
            $query->where('include_in_reports', true);
        }

        if ($isDefault) {
            $query->where('is_default', true);
        }

        $query->orderBy('code');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $this->mapCollectionToEntities($paginator->items()),
            'meta' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }

    public function findAllByType(CategoryType $type): array
    {
        $models = CategoryModel::where('type', $type->value)
            ->orderBy('code')
            ->get();

        return $this->mapCollectionToEntities($models->all());
    }

    public function findAllRevenue(): array
    {
        return $this->findAllByType(CategoryType::REVENUE);
    }

    public function findAllExpense(): array
    {
        return $this->findAllByType(CategoryType::EXPENSE);
    }

    public function findAllTransfer(): array
    {
        return $this->findAllByType(CategoryType::TRANSFER);
    }

    public function findAllRoot(): array
    {
        $models = CategoryModel::whereNull('parent_id')
            ->orderBy('code')
            ->get();

        return $this->mapCollectionToEntities($models->all());
    }

    public function findAllChildren(CategoryId $parentId): array
    {
        $models = CategoryModel::where('parent_id', $parentId->toString())
            ->orderBy('code')
            ->get();

        return $this->mapCollectionToEntities($models->all());
    }

    public function findTree(?CategoryType $type = null): array
    {
        $query = CategoryModel::query();

        if ($type) {
            $query->where('type', $type->value);
        }

        $query->whereNull('parent_id')
            ->orderBy('code')
            ->with('children');

        $models = $query->get();

        return $this->buildTree($models->all());
    }

    public function save(Category $category): void
    {
        $data = [
            'id' => $category->getId()->toString(),
            'name' => $category->getName(),
            'type' => $category->getType()->value,
            'code' => $category->getCode(),
            'description' => $category->getDescription(),
            'color' => $category->getColor(),
            'icon' => $category->getIcon(),
            'is_operating' => $category->isOperating(),
            'is_tax_deductible' => $category->isTaxDeductible(),
            'include_in_reports' => $category->isIncludeInReports(),
            'is_default' => $category->isDefault(),
            'parent_id' => $category->getParentId()?->toString(),
            'created_at' => $category->getCreatedAt(),
            'updated_at' => $category->getUpdatedAt(),
        ];

        if ($category->getDeletedAt()) {
            $data['deleted_at'] = $category->getDeletedAt();
        }

        CategoryModel::updateOrCreate(['id' => $category->getId()->toString()], $data);
    }

    public function delete(Category $category): void
    {
        $model = CategoryModel::find($category->getId()->toString());

        if ($model) {
            $model->delete();
        }
    }

    public function existsWithCode(string $code, ?CategoryId $excludeId = null): bool
    {
        $query = CategoryModel::where('code', $code);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId->toString());
        }

        return $query->exists();
    }

    public function existsWithName(string $name, ?CategoryId $excludeId = null): bool
    {
        $query = CategoryModel::where('name', $name);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId->toString());
        }

        return $query->exists();
    }

    public function countByType(CategoryType $type): int
    {
        return CategoryModel::where('type', $type->value)->count();
    }

    public function countByParent(CategoryId $parentId): int
    {
        return CategoryModel::where('parent_id', $parentId->toString())->count();
    }

    public function getDefaultCategories(): array
    {
        $models = CategoryModel::where('is_default', true)
            ->orderBy('type')
            ->orderBy('code')
            ->get();

        return $this->mapCollectionToEntities($models->all());
    }

    public function hasChildren(CategoryId $id): bool
    {
        return CategoryModel::where('parent_id', $id->toString())->exists();
    }

    public function hasTransactions(CategoryId $id): bool
    {
        return \App\Infrastructure\Persistence\Eloquent\Models\TransactionModel::where('category_id', $id->toString())->exists();
    }

    private function mapToEntity(CategoryModel $model): Category
    {
        return new Category(
            id: CategoryId::fromString($model->id),
            name: $model->name,
            type: CategoryType::from($model->type),
            code: $model->code,
            description: $model->description,
            color: $model->color,
            icon: $model->icon,
            isOperating: $model->is_operating,
            isTaxDeductible: $model->is_tax_deductible,
            includeInReports: $model->include_in_reports,
            isDefault: $model->is_default,
            parentId: $model->parent_id ? CategoryId::fromString($model->parent_id) : null,
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at),
        );
    }

    private function mapCollectionToEntities(array $models): array
    {
        return array_map(
            fn (CategoryModel $model) => $this->mapToEntity($model),
            $models
        );
    }

    private function buildTree(array $models): array
    {
        $tree = [];

        foreach ($models as $model) {
            $entity = $this->mapToEntity($model);
            $children = [];

            if ($model->children) {
                $children = $this->buildTree($model->children->all());
            }

            $tree[] = [
                'category' => $entity,
                'children' => $children,
            ];
        }

        return $tree;
    }
}
