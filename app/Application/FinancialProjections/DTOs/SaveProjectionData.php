<?php

declare(strict_types=1);

namespace App\Application\FinancialProjections\DTOs;

use App\Domain\FinancialProjections\ProjectionType;

final readonly class SaveProjectionData
{
    public function __construct(
        public string $id,
        public ProjectionType|string $type,
        public string $periodType,
        public string $yearMonth = '',
        public string $year = '',
        public int $quarter = 0,
        public ?string $categoryId = null,
        public string $scenario = 'base',
        public string $title = '',
        public array $items = [],
        public ?string $notes = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->id)) {
            throw new \InvalidArgumentException('Projection ID is required.');
        }
    }
}
