<?php

declare(strict_types=1);

namespace App\Domain\Plans;

interface PlanRepositoryInterface
{
    public function findBySlug(string $slug): ?Plan;
}
