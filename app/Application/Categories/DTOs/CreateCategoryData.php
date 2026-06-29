<?php

declare(strict_types=1);

namespace App\Application\Categories\DTOs;

use App\Domain\Categories\ValueObjects\CategoryId;
use App\Domain\Categories\ValueObjects\CategoryType;

final class CreateCategoryData
{
    public function __construct(
        public readonly string $name,
        public readonly CategoryType $type,
        public readonly string $code,
        public readonly ?string $description = null,
        public readonly ?string $color = null,
        public readonly ?string $icon = null,
        public readonly bool $isOperating = true,
        public readonly bool $isTaxDeductible = false,
        public readonly bool $includeInReports = true,
        public readonly bool $isDefault = false,
        public readonly ?CategoryId $parentId = null,
    ) {}
}
