<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Plans\Plan;
use App\Domain\Plans\PlanRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\PlanModel;

final readonly class EloquentPlanRepository implements PlanRepositoryInterface
{
    public function findBySlug(string $slug): ?Plan
    {
        $model = PlanModel::query()->where('slug', $slug)->first();

        if ($model === null) {
            return null;
        }

        return new Plan(
            id: (int) $model->getAttribute('id'),
            slug: (string) $model->getAttribute('slug'),
            name: (string) $model->getAttribute('name'),
        );
    }
}
