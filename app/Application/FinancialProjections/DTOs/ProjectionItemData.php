<?php

declare(strict_types=1);

namespace App\Application\FinancialProjections\DTOs;

use App\Domain\FinancialProjections\ProjectionType;

final readonly class ProjectionItemData
{
    public function __construct(
        public string $date,
        public string $description,
        public string $amount,
        public ProjectionType|string $type,
        public ?string $categoryId = null,
        public ?string $categoryName = null,
        public ?string $notes = null,
        public ?string $source = null,
    ) {}
}
