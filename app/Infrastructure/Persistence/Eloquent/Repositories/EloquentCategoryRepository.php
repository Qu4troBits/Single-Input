<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Categories\Category;
use App\Domain\Categories\CategoryId;
use App\Domain\Categories\CategoryRepositoryInterface;
use App\Domain\Categories\CategoryStatus;
use App\Domain\Categories\CategoryType;
use App\Infrastructure\Persistence\Eloquent\Models\CategoryModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function save(Category $category): void
    {
        CategoryModel::updateOrCreate(
            ['id' => $category->getId()->toString()],
            [
                'name' => $category->getName(),
                'type' => $category->getType()->value,
                'status' => $category->getStatus()->value,
                'color' => $category->getColor(),
                'icon' => $category->getIcon(),
                'parent_id' => $category->getParentId()?->toString(),
                'created_at' => $category->getCreatedAt(),
                'updated_at' => $category->getUpdatedAt(),
            ]
        );
    }

    public function findById(CategoryId $id): ?Category
    {
        try {
            $model = CategoryModel::findOrFail($id->toString());
            return $this->toDomain($model);
        } catch (ModelNotFoundException) {
            return null;
        }
    }

    /** @return array<Category> */
    public function findAll(): array
    {
        return CategoryModel::all()
            ->map(fn (CategoryModel $model) => $this->toDomain($model))
            ->toArray();
    }

    /** @return array<Category> */
    public function findByType(CategoryType $type): array
    {
        return CategoryModel::where('type', $type->value)
            ->get()
            ->map(fn (CategoryModel $model) => $this->toDomain($model))
            ->toArray();
    }

    /** @return array<Category> */
    public function findByStatus(CategoryStatus $status): array
    {
        return CategoryModel::where('status', $status->value)
            ->get()
            ->map(fn (CategoryModel $model) => $this->toDomain($model))
            ->toArray();
    }

    /** @return array<Category> */
    public function findByParentId(?CategoryId $parentId): array
    {
        $query = CategoryModel::query();
        
        if ($parentId === null) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parentId->toString());
        }
        
        return $query->get()
            ->map(fn (CategoryModel $model) => $this->toDomain($model))
            ->toArray();
    }

    /** @return array<Category> */
    public function findActiveByType(CategoryType $type): array
    {
        return CategoryModel::where('type', $type->value)
            ->where('status', CategoryStatus::ACTIVE->value)
            ->get()
            ->map(fn (CategoryModel $model) => $this->toDomain($model))
            ->toArray();
    }

    public function delete(CategoryId $id): void
    {
        CategoryModel::where('id', $id->toString())->delete();
    }

    private function toDomain(CategoryModel $model): Category
    {
        return new Category(
            id: CategoryId::fromString($model->id),
            name: $model->name,
            type: CategoryType::from($model->type),
            status: CategoryStatus::from($model->status),
            color: $model->color,
            icon: $model->icon,
            parentId: $model->parent_id ? CategoryId::fromString($model->parent_id) : null,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }
}