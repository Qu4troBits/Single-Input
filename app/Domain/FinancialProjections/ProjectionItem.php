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
            'amount' => $this->amount->toNumeric(),
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
            throw new \InvalidArgumentException('ID cannot be empty.');
        }

        if (empty($this->description)) {
            throw new \InvalidArgumentException('Description cannot be empty.');
        }

        if (mb_strlen($this->description) > 255) {
            throw new \InvalidArgumentException('Description cannot exceed 255 characters.');
        }

        if ($this->amount->isNegative()) {
            throw new \InvalidArgumentException('Amount cannot be negative.');
        }

        if ($this->categoryName && mb_strlen($this->categoryName) > 100) {
            throw new \InvalidArgumentException('Category name cannot exceed 100 characters.');
        }

        if ($this->source && mb_strlen($this->source) > 50) {
            throw new \InvalidArgumentException('Source cannot exceed 50 characters.');
        }
    }

    public function getFormattedDate(): string
    {
        return $this->date->format('d/m/Y');
    }

    public function getFormattedAmount(): string
    {
        $num = (float)$this->amount->toNumeric();
        return 'R$ ' . number_format($num, 2, ',', '.');
    }

    public function update(array $data): self
    {
        return new self(
            id: $this->id,
            date: isset($data['date']) ? \DateTimeImmutable::createFromFormat('Y-m-d', $data['date']) : $this->date,
            description: $data['description'] ?? $this->description,
            amount: isset($data['amount']) ? ($data['amount'] instanceof Money ? $data['amount'] : Money::of($data['amount'])) : $this->amount,
            type: isset($data['type']) ? ($data['type'] instanceof ProjectionType ? $data['type'] : ProjectionType::from($data['type'])) : $this->type,
            categoryId: $data['categoryId'] ?? $data['category_id'] ?? $this->categoryId,
            categoryName: $data['categoryName'] ?? $data['category_name'] ?? $this->categoryName,
            notes: $data['notes'] ?? $this->notes,
            source: $data['source'] ?? $this->source,
        );
    }
}
