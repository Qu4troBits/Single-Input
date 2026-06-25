<?php

declare(strict_types=1);

namespace App\Application\Categories\Data;

use App\Domain\Categories\CategoryId;
use App\Domain\Categories\CategoryStatus;
use App\Domain\Categories\CategoryType;

final readonly class UpdateCategoryData
{
    public function __construct(
        public string $name,
        public CategoryType $type,
        public CategoryStatus $status,
        public ?string $color,
        public ?string $icon,
        public ?CategoryId $parentId,
    ) {}
}