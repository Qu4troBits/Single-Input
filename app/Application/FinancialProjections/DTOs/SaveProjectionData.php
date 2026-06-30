<?php

declare(strict_types=1);

namespace App\Application\FinancialProjections\DTOs;

use App\Domain\FinancialProjections\ProjectionType;

final readonly class SaveProjectionData
{
    /**
     * @param array<ProjectionItemData> $items
     */
    public function __construct(
        public string $id,
        public ProjectionType $type,
        public string $periodType,
        public string $yearMonth = '',
        public string $year = '',
        public int $quarter = 0,
        public ?string $categoryId = null,
        public string $scenario = 'base',
        public string $title,
        public array $items,
        public ?string $notes = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->id)) {
            throw new \InvalidArgumentException('Projection ID cannot be empty.');
        }

        if (empty($this->title)) {
            throw new \InvalidArgumentException('Projection title cannot be empty.');
        }

        if (empty($this->items)) {
            throw new \InvalidArgumentException('At least one projection item is required.');
        }

        foreach ($this->items as $item) {
            if (!$item instanceof ProjectionItemData) {
                throw new \InvalidArgumentException('All items must be instances of ProjectionItemData.');
            }
        }
    }
}

final readonly class ProjectionItemData
{
    public function __construct(
        public string $id,
        public string $date,
        public string $description,
        public string $amount,
        public ProjectionType $type,
        public ?string $categoryId = null,
        public ?string $categoryName = null,
        public ?string $notes = null,
        public ?string $source = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->id)) {
            throw new \InvalidArgumentException('Item ID cannot be empty.');
        }

        if (empty($this->description)) {
            throw new \InvalidArgumentException('Item description cannot be empty.');
        }

        if (!is_numeric($this->amount)) {
            throw new \InvalidArgumentException('Amount must be a valid numeric string.');
        }
    }
}