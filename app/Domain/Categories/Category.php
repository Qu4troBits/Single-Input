<?php

declare(strict_types=1);

namespace App\Domain\Categories;

final class Category
{
    public function __construct(
        private readonly CategoryId $id,
        private string $name,
        private CategoryType $type,
        private CategoryStatus $status,
        private ?string $color,
        private ?string $icon,
        private ?CategoryId $parentId,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {}

    public function getId(): CategoryId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): CategoryType
    {
        return $this->type;
    }

    public function getStatus(): CategoryStatus
    {
        return $this->status;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getParentId(): ?CategoryId
    {
        return $this->parentId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function update(
        string $name,
        CategoryType $type,
        CategoryStatus $status,
        ?string $color,
        ?string $icon,
        ?CategoryId $parentId,
    ): void {
        $this->name = $name;
        $this->type = $type;
        $this->status = $status;
        $this->color = $color;
        $this->icon = $icon;
        $this->parentId = $parentId;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->status = CategoryStatus::ACTIVE;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->status = CategoryStatus::INACTIVE;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function archive(): void
    {
        $this->status = CategoryStatus::ARCHIVED;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isActive(): bool
    {
        return $this->status === CategoryStatus::ACTIVE;
    }

    public function isIncome(): bool
    {
        return $this->type === CategoryType::INCOME;
    }

    public function isExpense(): bool
    {
        return $this->type === CategoryType::EXPENSE;
    }

    public function isTransfer(): bool
    {
        return $this->type === CategoryType::TRANSFER;
    }

    public function hasParent(): bool
    {
        return $this->parentId !== null;
    }
}