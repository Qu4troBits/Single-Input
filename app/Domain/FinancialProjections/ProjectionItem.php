<?php

declare(strict_types=1);

namespace App\Domain\FinancialProjections;

use App\Domain\Shared\Money;

final readonly class ProjectionItem
{
    public function __construct(
        private string $id,
        private \DateTimeImmutable $date,
        private string $description,
        private Money $amount,
        private ProjectionType $type,
        private ?string $categoryId = null,
        private ?string $categoryName = null,
        private ?string $notes = null,
        private ?string $source = null, // 'historical', 'manual', 'formula'
    ) {
        $this->validate();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getType(): ProjectionType
    {
        return $this->type;
    }

    public function getCategoryId(): ?string
    {
        return $this->categoryId;
    }

    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function isRevenue(): bool
    {
        return $this->type === ProjectionType::REVENUE;
    }

    public function isExpense(): bool
    {
        return $this->type === ProjectionType::EXPENSE;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->format('Y-m-d'),
            'description' => $this->description,
            'amount' => $this->amount->getAmount(),
            'type' => $this->type->value,
            'category_id' => $this->categoryId,
            'category_name' => $this->categoryName,
            'notes' => $this->notes,
            'source' => $this->source,
        ];
    }

    private function validate(): void
    {
        if (empty($this->id)) {
            throw new \InvalidArgumentException('Projection item ID cannot be empty.');
        }

        if (empty($this->description)) {
            throw new \InvalidArgumentException('Projection item description cannot be empty.');
        }
    }
}