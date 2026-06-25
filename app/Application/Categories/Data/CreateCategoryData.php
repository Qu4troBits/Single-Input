<?php

declare(strict_types=1);

namespace App\Application\Categories\Data;

use App\Domain\Categories\CategoryId;
use App\Domain\Categories\CategoryType;

final readonly class CreateCategoryData
{
    public function __construct(
        public string $name,
        public CategoryType $type,
        public ?string $color,
        public ?string $icon,
        public ?CategoryId $parentId,
    ) {}
}